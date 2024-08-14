<?php

namespace App\Imports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class CategoriesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Category([
            'name' => $row['name'],
            'slug' => Str::slug($row['name']),
            'description' => $row['description'] ?? null,
        ]);
    }
}
