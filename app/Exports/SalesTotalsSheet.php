<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SalesTotalsSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        private readonly Collection $sales,
        private readonly ?string $from,
        private readonly ?string $to
    ) {}

    public function title(): string
    {
        return 'Summary Totals';
    }

    public function array(): array
    {
        $s = $this->sales;

        $period = match(true) {
            $this->from && $this->to => $this->from . ' to ' . $this->to,
            $this->from              => 'From ' . $this->from,
            $this->to                => 'Up to ' . $this->to,
            default                  => 'All Time',
        };

        $byStatus = $s->groupBy('payment_status');
        $byType   = $s->groupBy('sale_type');

        return [
            // Title block
            ['SALES SUMMARY REPORT'],
            ['Period:', $period],
            ['Generated:', now()->format('d/m/Y H:i')],
            ['Total Records:', $s->count()],
            [''],

            // Column headers
            ['Metric', 'Amount (₹)', 'Count'],

            // Core amounts
            ['── AMOUNTS ────────────────────────────', '', ''],
            ['Gross Subtotal',        $s->sum(fn($r) => (float)($r->subtotal ?? $r->total)), ''],
            ['Total Discount',        $s->sum(fn($r) => (float)($r->discount ?? 0)),         ''],
            ['Total Exchange',        $s->sum(fn($r) => (float)($r->exchange ?? 0)),         ''],
            ['Net Payable (Total)',   $s->sum(fn($r) => (float)($r->total ?? 0)),            $s->count()],

            ['── COLLECTIONS ────────────────────────', '', ''],
            ['Cash Collected',        $s->sum(fn($r) => (float)($r->cash_amount ?? 0)),      ''],
            ['Advance Collected',     $s->sum(fn($r) => (float)($r->advance_amount ?? 0)),   ''],
            ['Finance Amount',        $s->sum(fn($r) => (float)($r->finance_amount ?? 0)),   ''],
            ['Total Amount Paid',     $s->sum(fn($r) => (float)($r->amount_paid ?? 0)),      ''],
            ['Balance Due (Unpaid)',  $s->sum(fn($r) => (float)($r->balance_amount ?? 0)),   ''],

            ['── PAYMENT STATUS ─────────────────────', '', ''],
            ['Paid',                  $byStatus->get('paid',    collect())->sum(fn($r)=>(float)$r->total), $byStatus->get('paid',    collect())->count()],
            ['Partial',               $byStatus->get('partial', collect())->sum(fn($r)=>(float)$r->total), $byStatus->get('partial', collect())->count()],
            ['Unpaid',                $byStatus->get('unpaid',  collect())->sum(fn($r)=>(float)$r->total), $byStatus->get('unpaid',  collect())->count()],

            ['── BY SALE TYPE ───────────────────────', '', ''],
            ['Vehicle Sales',         $byType->get('vehicle', collect())->sum(fn($r)=>(float)$r->total),   $byType->get('vehicle', collect())->count()],
            ['Parts / Other',         $byType->get('parts',   collect())->sum(fn($r)=>(float)$r->total),   $byType->get('parts',   collect())->count()],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            6 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 36,
            'B' => 20,
            'C' => 10,
        ];
    }
}
