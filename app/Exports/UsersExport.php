<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UsersExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $isTemplate;

    public function __construct($isTemplate = false)
    {
        $this->isTemplate = $isTemplate;
    }

    public function collection()
    {
        if ($this->isTemplate) {
            return collect([]);
        }

        return User::select('name', 'email', 'image')
            ->get()
            ->map(function ($user) {
                $user->image = url('storage/users/' . basename($user->image));
                return $user;
            });
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Image URL',
        ];
    }
}


// namespace App\Exports;

// use App\Models\User;
// use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\ShouldAutoSize;
// use Maatwebsite\Excel\Concerns\WithDrawings;
// use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

// class UsersExport implements FromCollection, WithHeadings, ShouldAutoSize, WithDrawings
// {
//     protected $isTemplate;

//     public function __construct($isTemplate = false)
//     {
//         $this->isTemplate = $isTemplate;
//     }

//     public function collection()
//     {
//         if ($this->isTemplate) {
//             return collect([]);
//         }
//         return User::select('name', 'email')->get();
//     }

//     public function headings(): array
//     {
//         return [
//             'Name',
//             'Email',
//             'Image',
//         ];
//     }

//     public function drawings()
//     {
//         $drawings = [];
//         $users = $this->isTemplate ? collect([]) : User::all();

//         foreach ($users as $index => $user) {
//             $imagePath = storage_path('app/public/users/' . basename($user->image));

//             if (file_exists($imagePath)) {
//                 $drawing = new Drawing();
//                 $drawing->setName($user->name);
//                 $drawing->setDescription($user->name);
//                 $drawing->setPath($imagePath);
//                 $drawing->setHeight(50);
//                 $drawing->setCoordinates('C' . ($index + 2));

//                 $drawings[] = $drawing;
//             }
//         }

//         return $drawings;
//     }
// }
