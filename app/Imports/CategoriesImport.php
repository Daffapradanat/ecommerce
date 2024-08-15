<?php

namespace App\Imports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoriesImport implements ToModel, WithHeadingRow, SkipsOnFailure, WithValidation
{
    use SkipsFailures;

    public $errors = [];

    public function model(array $row)
    {
        if (empty($row['name'])) {
            return null;
        }

        return new Category([
            'name' => $row['name'],
            'slug' => Str::slug($row['name']),
            'description' => $row['description'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name'),
            ],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.unique' => 'The category name ":input" already exists.',
        ];
    }
}
