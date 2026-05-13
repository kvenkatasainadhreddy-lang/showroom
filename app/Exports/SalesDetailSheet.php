<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SalesDetailSheet implements
    FromCollection, WithHeadings, WithMapping, WithTitle,
    WithStyles, WithColumnWidths
{
    public function __construct(private readonly Collection $sales) {}

    public function title(): string
    {
        return 'All Sales';
    }

    public function collection(): Collection
    {
        return $this->sales;
    }

    public function headings(): array
    {
        return [
            '#',
            'Invoice No',
            'Date',
            'Customer Name',
            'Branch',
            'Salesman',
            'Sale Type',
            'Subtotal (₹)',
            'Discount (₹)',
            'Exchange (₹)',
            'Net Payable (₹)',
            'Cash (₹)',
            'Advance (₹)',
            'Finance Name',
            'Finance Amount (₹)',
            'Amount Paid (₹)',
            'Balance Due (₹)',
            'Payment Status',
            'Notes',
        ];
    }

    public function map($sale): array
    {
        static $rowNum = 0;
        $rowNum++;

        return [
            $rowNum,
            $sale->invoice_no,
            $sale->sale_date?->format('d/m/Y') ?? '',
            $sale->customer_name ?? $sale->customer?->name ?? 'Walk-in',
            $sale->branch?->name ?? '—',
            $sale->soldBy?->name ?? '—',
            ucfirst($sale->sale_type ?? 'parts'),
            (float)($sale->subtotal   ?? $sale->total),
            (float)($sale->discount   ?? 0),
            (float)($sale->exchange   ?? 0),
            (float)($sale->total      ?? 0),
            (float)($sale->cash_amount    ?? 0),
            (float)($sale->advance_amount ?? 0),
            $sale->finance_name ?? '—',
            (float)($sale->finance_amount ?? 0),
            (float)($sale->amount_paid    ?? 0),
            (float)($sale->balance_amount ?? 0),
            ucfirst($sale->payment_status ?? ''),
            $sale->notes ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 18,
            'C' => 12,
            'D' => 22,
            'E' => 16,
            'F' => 16,
            'G' => 10,
            'H' => 15,
            'I' => 14,
            'J' => 14,
            'K' => 16,
            'L' => 13,
            'M' => 13,
            'N' => 18,
            'O' => 18,
            'P' => 14,
            'Q' => 14,
            'R' => 15,
            'S' => 30,
        ];
    }
}
