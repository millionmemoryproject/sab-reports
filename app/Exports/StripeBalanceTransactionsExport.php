<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StripeBalanceTransactionsExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected array $rows
    ) {}

    public function collection()
    {
        return new Collection($this->rows);
    }

    public function headings(): array
    {
        return [
            'Stripe Transaction ID',
            'Created',
            'Available On',
            'Type',
            'Reporting Category',
            'Description',
            'Currency',
            'Gross',
            'Fee',
            'Net',
            'Status',
            'Source ID',
        ];
    }
}
