<?php

namespace App\Exports;

use App\Models\Buyer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BuyersExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function collection()
    {
        return Buyer::where('status', 'active')
            ->select('id', 'name', 'email', 'status', 'image')
            ->get()
            ->map(function ($buyer) {
                $buyer->image = url('storage/buyers/'.basename($buyer->image));

                return $buyer;
            });
    }

    public function headings(): array
    {
        return [
            'Id',
            'Name',
            'Email',
            'Status',
            'Image',
        ];
    }
}
