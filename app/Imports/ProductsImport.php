<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Image;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Storage;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $product = Product::create([
            'name' => $row['name'],
            'description' => $row['description'],
            'price' => $row['price'],
            'stock' => $row['stock'],
            'category_id' => $row['category_id'],
        ]);

        if (!empty($row['image'])) {
            $imagePaths = explode(',', $row['image']);
            foreach ($imagePaths as $imagePath) {
                $imagePath = trim($imagePath);

                try {
                    $fileName = basename($imagePath);
                    $storagePath = 'product_images/' . $fileName;
                    if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                        $fileContent = file_get_contents($imagePath);
                        Storage::disk('public')->put($storagePath, $fileContent);
                    }
                    else {
                        continue;
                    }
                    Image::create([
                        'product_id' => $product->id,
                        'path' => $storagePath,
                    ]);
                } catch (\Exception $e) {
                }
            }
        }

        return $product;
    }

    public function rules(): array
    {
        return [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable',
        ];
    }
}
