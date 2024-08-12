<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Image;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $product = Product::create([
            'name' => $row['nama'],
            'description' => $row['description'],
            'price' => $row['price'],
            'stock' => $row['stock'],
            'category_id' => $row['category_id'],
        ]);

        if (!empty($row['image'])) {
            $imagePaths = explode(',', $row['image']);
            foreach ($imagePaths as $imagePath) {
                $imagePath = trim($imagePath);

                if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                    $imageContent = @file_get_contents($imagePath);
                } else {
                    $imageContent = @file_get_contents($imagePath);
                }

                if ($imageContent !== false) {
                    $fileName = basename($imagePath);
                    $newPath = 'product_images/' . uniqid() . '_' . $fileName;
                    if (Storage::disk('public')->put($newPath, $imageContent)) {
                        Image::create([
                            'product_id' => $product->id,
                            'path' => $newPath,
                        ]);
                    }
                } else {
                    Log::error("Unable to read image file: " . $imagePath);
                }
            }
        }

        return $product;
    }

    public function rules(): array
    {
        return [
            'nama' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable',
        ];
    }

    public function onFailure(\Maatwebsite\Excel\Validators\Failure ...$failures)
    {
        foreach ($failures as $failure) {
            Log::error("Row: {$failure->row()}; Attribute: {$failure->attribute()}; Error: " . implode(', ', $failure->errors()));
        }
    }
}
