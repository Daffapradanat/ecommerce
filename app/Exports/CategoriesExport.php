<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoriesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Category::select('name', 'slug', 'description')->get();
    }

    public function headings(): array
    {
        return [
            'Name',
            'Slug',
            'Description',
        ];
    }
}
