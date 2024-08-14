<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoriesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Category::select('id','name', 'slug', 'description')->get();
    }

    public function headings(): array
    {
        return [
            'Id',
            'Name',
            'Slug',
            'Description',
        ];
    }
}
