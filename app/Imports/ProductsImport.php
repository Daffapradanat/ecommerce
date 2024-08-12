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
                    $fileName = time() . '_' . basename($imagePath);
                    $storagePath = 'product_images/' . $fileName;
                    if (Storage::disk('public')->exists($imagePath)) {
                        Storage::disk('public')->copy($imagePath, $storagePath);

                        Image::create([
                            'product_id' => $product->id,
                            'path' => $storagePath,
                        ]);
                        Storage::disk('public')->delete($imagePath);
                    }
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
