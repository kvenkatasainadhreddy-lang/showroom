<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalesExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        public readonly ?string $from,
        public readonly ?string $to
    ) {}

    public function sheets(): array
    {
        $query = Sale::with('branch', 'soldBy')
            ->when($this->from, fn($q) => $q->whereDate('sale_date', '>=', $this->from))
            ->when($this->to,   fn($q) => $q->whereDate('sale_date', '<=', $this->to))
            ->orderBy('sale_date')
            ->orderBy('id');

        $sales = $query->get();

        return [
            new SalesDetailSheet($sales),
            new SalesTotalsSheet($sales, $this->from, $this->to),
        ];
    }
}
