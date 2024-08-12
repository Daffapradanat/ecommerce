<?php

namespace App\Exports;

use App\Models\Buyer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BuyersExport implements FromQuery, WithHeadings, ShouldAutoSize
{
    public function query()
    {
        return Buyer::query()
            ->where('status', 'active')
            ->select('id', 'name', 'email', 'status', 'created_at', 'updated_at', 'image');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Status',
            'Created At',
            'Updated At',
            'Image URL',
        ];
    }

    public function collection()
    {
        return $this->query()
            ->get()
            ->map(function ($buyer) {
                $buyer->image = url('storage/buyers/' . basename($buyer->image));
                return $buyer;
            });
    }
}

// namespace App\Exports;

// use App\Models\Buyer;
// use Maatwebsite\Excel\Concerns\FromQuery;
// use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\ShouldAutoSize;
// use Maatwebsite\Excel\Concerns\WithDrawings;
// use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// class BuyersExport implements FromQuery, WithHeadings, ShouldAutoSize, WithDrawings
// {
//     public function query()
//     {
//         return Buyer::query()->where('status', 'active')->select('id', 'name', 'email', 'status', 'created_at', 'updated_at');
//     }

//     public function headings(): array
//     {
//         return [
//             'ID',
//             'Name',
//             'Email',
//             'Status',
//             'Created At',
//             'Updated At',
//             'Image',
//         ];
//     }

//     public function drawings()
//     {
//         $drawings = [];
//         $buyers = Buyer::where('status', 'active')->get();

//         foreach ($buyers as $index => $buyer) {
//             $imagePath = storage_path('app/public/buyers/' . basename($buyer->image));

//             if (file_exists($imagePath)) {
//                 $drawing = new Drawing();
//                 $drawing->setName($buyer->name);
//                 $drawing->setDescription($buyer->name);
//                 $drawing->setPath($imagePath);
//                 $drawing->setHeight(50);
//                 $drawing->setCoordinates('G' . ($index + 2));

//                 $drawings[] = $drawing;
//             }
//         }

//         return $drawings;
//     }
// }
