<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Image;
use App\Models\Catagory;
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
                $newPath = $this->processImage($imagePath, $product->id);
                if ($newPath) {
                    Image::create([
                        'product_id' => $product->id,
                        'path' => $newPath,
                    ]);
                }
            }
        }

        return $product;
    }

    private function processImage($imagePath, $productId)
    {
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $response = Http::get($imagePath);
            if ($response->successful()) {
                $fileName = basename($imagePath);
                $newPath = 'product_images/' . $productId . '_' . uniqid() . '_' . $fileName;
                Storage::disk('public')->put($newPath, $response->body());
                return $newPath;
            }
        }
        elseif (file_exists($imagePath)) {
            $fileName = basename($imagePath);
            $newPath = 'product_images/' . $productId . '_' . uniqid() . '_' . $fileName;
            Storage::disk('public')->put($newPath, file_get_contents($imagePath));
            return $newPath;
        }
        elseif (Storage::disk('public')->exists('product_images/' . $imagePath)) {
            return 'product_images/' . $imagePath;
        }
        return null;
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
