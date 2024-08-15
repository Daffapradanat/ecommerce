<?php

namespace App\Imports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Support\Str;

class CategoriesImport implements ToModel, WithHeadingRow, SkipsOnFailure
{
    use SkipsFailures;

    public $errors = [];

    public function model(array $row)
    {
        if (empty($row['name'])) {
            return null;
        }
        $existingCategory = Category::where('name', $row['name'])->first();

        if ($existingCategory) {
            $this->errors[] = "Category with name '{$row['name']}' already exists at row ".($row['_row'] ?? 'unknown').".";
            return null;
        }

        return new Category([
            'name' => $row['name'],
            'slug' => Str::slug($row['name']),
            'description' => $row['description'] ?? null,
        ]);
    }
}
