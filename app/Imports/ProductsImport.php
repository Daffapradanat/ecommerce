<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

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

        if (! empty($row['image'])) {
            $imagePath = $this->processImage($row['image']);
            if ($imagePath) {
                $product->image()->create(['path' => $imagePath]);
            }
        }

        return $product;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name'),
            ],
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|string',
        ];
    }

    private function processImage($imagePath)
    {
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $response = Http::get($imagePath);
            if ($response->successful()) {
                $filename = basename($imagePath);
                $path = 'product_images/'.uniqid().'_'.$filename;
                Storage::disk('public')->put($path, $response->body());

                return $path;
            }
        } elseif (file_exists($imagePath)) {
            $filename = basename($imagePath);
            $path = 'product_images/'.uniqid().'_'.$filename;
            Storage::disk('public')->put($path, file_get_contents($imagePath));

            return $path;
        }

        return null;
    }
}
