<?php

namespace App\Imports;

use App\Models\VehicleModel;
use App\Models\VehicleVariant;
use App\Models\VehicleStock;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class VehicleStockImport implements ToCollection, WithHeadingRow
{

    public int   $imported   = 0;
    public int   $updated    = 0;
    public int   $skipped    = 0;
    public array $rowErrors  = [];   // renamed to avoid conflict with SkipsErrors trait
    public array $rows       = [];   // preview rows used by dry-run

    private int    $brandId;
    private ?int   $branchId;
    private string $receivedDate;
    private bool   $dryRun;

    public function __construct(int $brandId, ?int $branchId, string $receivedDate, bool $dryRun = false)
    {
        $this->brandId      = $brandId;
        $this->branchId     = $branchId;
        $this->receivedDate = $receivedDate;
        $this->dryRun       = $dryRun;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +2 because row 1 is header

            try {
                // ── Normalise column names (handles variations in header spelling)
                $chassis   = $this->cell($row, ['chasis_no', 'chassis_no', 'chassis', 'chasis']);
                $engine    = $this->cell($row, ['engine_no', 'engine']);
                $modelName = $this->cell($row, ['model']);
                $varName   = $this->cell($row, ['variant']);
                $color     = $this->cell($row, ['colour', 'color']);

                // ── Skip if mandatory fields are empty
                if (!$chassis || !$modelName || !$varName) {
                    $this->rowErrors[] = [
                        'row'     => $rowNum,
                        'chassis' => $chassis ?: '(blank)',
                        'message' => 'Missing chassis, model or variant — row skipped.',
                    ];
                    $this->skipped++;
                    continue;
                }

                // ── Store for preview
                $this->rows[] = [
                    'row'     => $rowNum,
                    'chassis' => $chassis,
                    'engine'  => $engine,
                    'model'   => $modelName,
                    'variant' => $varName,
                    'color'   => $color,
                ];

                if ($this->dryRun) continue;

                // ── 1. Brand (already selected by user)
                // nothing to create — brand_id passed in constructor

                // ── 2. VehicleModel — find or create by name + brand
                $model = VehicleModel::firstOrCreate(
                    ['brand_id' => $this->brandId, 'name' => $modelName],
                    ['year' => now()->year]
                );

                // ── 3. VehicleVariant — upsert by name + model_id
                $variant = VehicleVariant::firstOrCreate(
                    ['model_id' => $model->id, 'name' => $varName],
                    ['color' => $color, 'is_active' => true]
                );
                // Update color if variant already exists but color was blank
                if (!$variant->wasRecentlyCreated && $color && !$variant->color) {
                    $variant->update(['color' => $color]);
                }

                // ── 4. VehicleStock — upsert on chassis_number
                $existing = VehicleStock::where('chassis_number', $chassis)->first();

                if ($existing) {
                    // Update engine / color but don't touch status/prices
                    $existing->update([
                        'engine_number' => $engine  ?: $existing->engine_number,
                        'color'         => $color   ?: $existing->color,
                        'variant_id'    => $variant->id,
                    ]);
                    $this->updated++;
                } else {
                    VehicleStock::create([
                        'variant_id'    => $variant->id,
                        'branch_id'     => $this->branchId,
                        'chassis_number'=> $chassis,
                        'engine_number' => $engine,
                        'color'         => $color,
                        'status'        => 'available',
                        'received_date' => $this->receivedDate,
                        'purchase_price'=> 0,
                        'selling_price' => 0,
                    ]);
                    $this->imported++;
                }

            } catch (\Throwable $e) {
                $this->rowErrors[] = [
                    'row'     => $rowNum,
                    'chassis' => $chassis ?? '?',
                    'message' => $e->getMessage(),
                ];
                $this->skipped++;
            }
        }
    }

    // ── Helper: find a cell value by trying multiple possible header names
    private function cell(Collection $row, array $keys): ?string
    {
        $map = $row->mapWithKeys(fn($v, $k) => [
            Str::lower(Str::snake(preg_replace('/\s+/', '_', trim((string)$k)))) => $v
        ]);

        foreach ($keys as $k) {
            if ($map->has($k) && !is_null($map[$k]) && trim((string)$map[$k]) !== '') {
                return trim((string)$map[$k]);
            }
        }
        return null;
    }
}
