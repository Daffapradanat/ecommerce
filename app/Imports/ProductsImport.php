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
                $fileName = basename($imagePath);
                $newPath = uniqid() . '_' . $fileName;

                // Coba untuk menyalin file jika ada
                if (file_exists($imagePath)) {
                    if (Storage::disk('public')->put($newPath, file_get_contents($imagePath))) {
                        Image::create([
                            'product_id' => $product->id,
                            'path' => $newPath,
                        ]);
                        Log::info("Image successfully copied for product {$product->id}: {$newPath}");
                    } else {
                        Log::error("Failed to copy image for product {$product->id}: {$imagePath}");
                    }
                } else {
                    // Jika file tidak ada, simpan path sebagai placeholder
                    Image::create([
                        'product_id' => $product->id,
                        'path' => $newPath,
                    ]);
                    Log::warning("Image file not found for product {$product->id}. Placeholder path saved: {$newPath}");
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
