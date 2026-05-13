<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\VehicleStockImport;
use App\Models\Brand;
use App\Models\Branch;
use App\Models\VehicleModel;
use App\Models\VehicleVariant;
use App\Models\VehicleStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class VehicleImportController extends Controller
{
    // ── Import page ─────────────────────────────────────────────
    public function index()
    {
        $brands   = Brand::orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        // Recent import logs (stored in session or a simple json log file)
        $history = $this->getHistory();

        return view('admin.vehicle-import.index', compact('brands', 'branches', 'history'));
    }

    // ── Dry-run preview (AJAX) ───────────────────────────────────
    public function preview(Request $request)
    {
        $request->validate([
            'file'     => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'brand_id' => 'required|exists:brands,id',
        ]);

        $importer = new VehicleStockImport(
            brandId:      (int)$request->brand_id,
            branchId:     $request->branch_id ? (int)$request->branch_id : null,
            receivedDate: $request->received_date ?: now()->toDateString(),
            dryRun:       true
        );

        Excel::import($importer, $request->file('file'));

        return response()->json([
            'rows'    => $importer->rows,
            'errors'  => $importer->rowErrors,
            'total'   => count($importer->rows) + count($importer->rowErrors),
        ]);
    }

    // ── Full import ──────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'file'          => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'brand_id'      => 'required|exists:brands,id',
            'branch_id'     => 'nullable|exists:branches,id',
            'received_date' => 'nullable|date',
        ]);

        $brand    = Brand::findOrFail($request->brand_id);
        $filename = $request->file('file')->getClientOriginalName();

        $importer = new VehicleStockImport(
            brandId:      (int)$request->brand_id,
            branchId:     $request->branch_id ? (int)$request->branch_id : null,
            receivedDate: $request->received_date ?: now()->toDateString(),
            dryRun:       false
        );

        Excel::import($importer, $request->file('file'));

        // ── Log to history ───────────────────────────────────────
        $this->logHistory([
            'at'       => now()->format('d M Y, H:i'),
            'file'     => $filename,
            'brand'    => $brand->name,
            'imported' => $importer->imported,
            'updated'  => $importer->updated,
            'skipped'  => $importer->skipped,
            'errors'   => count($importer->rowErrors),
            'by'       => auth()->user()->name,
        ]);

        $parts = [];
        if ($importer->imported) $parts[] = "{$importer->imported} new chassis added";
        if ($importer->updated)  $parts[] = "{$importer->updated} existing chassis updated";
        if ($importer->skipped)  $parts[] = "{$importer->skipped} rows skipped";

        $msg = implode(', ', $parts) . '.';

        if (count($importer->rowErrors) > 0) {
            return redirect()->route('admin.vehicle-import.index')
                ->with('warning', $msg . ' Some rows had errors — check below.')
                ->with('import_errors', $importer->rowErrors);
        }

        return redirect()->route('admin.vehicle-import.index')
            ->with('success', 'Import complete: ' . $msg);
    }

    // ── Download sample template ─────────────────────────────────
    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Vehicle Stock');

        // Headers
        $headers = ['S.NO', 'CHASIS NO', 'ENGINE NO', 'MODEL', 'VARIANT', 'COLOUR'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}1", $h);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $sheet->getStyle("{$col}1")->getFont()->setBold(true);
            $sheet->getStyle("{$col}1")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('1E3A5F');
            $sheet->getStyle("{$col}1")->getFont()->getColor()->setRGB('FFFFFF');
        }

        // Sample rows
        $samples = [
            [1, 'ME4JC94EDTG601668', 'JC94EG4136279', 'SP125 OBD2B', 'SP125 DLX DISK OBD2B', 'P BLACK'],
            [2, 'ME4JC85MDTG140239', 'JC85EG4367194', 'SHINE 125 OBD2B', 'SHINE 125 DISC-OBD2B', 'MAT AXIS GRAY METALL'],
            [3, 'ME4JC85NDTG219005', 'JC85EG4367290', 'SHINE 125 OBD2B', 'SHINE 125 DRUM-OBD2B', 'MAT AXIS GRAY METALL'],
        ];
        foreach ($samples as $row => $data) {
            foreach ($data as $col => $val) {
                $sheet->setCellValue(chr(65 + $col) . ($row + 1), $val);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $tmpFile = tempnam(sys_get_temp_dir(), 'vehicle_import_') . '.xlsx';
        $writer->save($tmpFile);

        return response()->download($tmpFile, 'vehicle_import_template.xlsx')->deleteFileAfterSend();
    }

    // ── Helpers ──────────────────────────────────────────────────
    private function historyPath(): string
    {
        return storage_path('app/vehicle_import_history.json');
    }

    private function getHistory(): array
    {
        if (!file_exists($this->historyPath())) return [];
        $data = json_decode(file_get_contents($this->historyPath()), true) ?? [];
        return array_slice(array_reverse($data), 0, 20); // last 20
    }

    private function logHistory(array $entry): void
    {
        $path = $this->historyPath();
        $history = file_exists($path) ? (json_decode(file_get_contents($path), true) ?? []) : [];
        $history[] = $entry;
        file_put_contents($path, json_encode($history, JSON_PRETTY_PRINT));
    }
}
