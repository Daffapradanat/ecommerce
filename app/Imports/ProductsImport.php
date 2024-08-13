<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductsImport implements SkipsOnFailure, ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    use Importable, SkipsFailures;

    private $existingProducts = [];
    private $rowCount = 0;

    public function __construct()
    {
        $this->existingProducts = Product::pluck('name')->toArray();
    }

    public function model(array $row)
    {
        $this->rowCount++;

        $product = new Product([
            'name' => $row['name'],
            'description' => $row['description'],
            'price' => $row['price'],
            'stock' => $row['stock'],
            'category_id' => $row['category_id'],
        ]);

        if (!empty($row['image'])) {
            $imagePath = $this->processImage($row['image'], $this->rowCount);
            if ($imagePath) {
                $product->image()->create(['path' => $imagePath]);
            }
        }

        return $product;
    }

    private function processImage($imagePath, $productId)
    {
        $storageFolder = storage_path('app/public/product_images');

        if (!File::isDirectory($storageFolder)) {
            File::makeDirectory($storageFolder, 0755, true, true);
        }

        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $fileName = $productId . '_' . uniqid() . '_' . basename($imagePath);
            $newPath = $storageFolder . '/' . $fileName;

            $imageContent = file_get_contents($imagePath);
            if ($imageContent !== false) {
                File::put($newPath, $imageContent);
                return 'product_images/' . $fileName;
            }
        } elseif (File::exists($imagePath)) {
            $fileName = $productId . '_' . uniqid() . '_' . basename($imagePath);
            $newPath = $storageFolder . '/' . $fileName;
            File::copy($imagePath, $newPath);
            return 'product_images/' . $fileName;
        }

        return null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'unique:products,name'],
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.unique' => 'The product ":input" already exists.',
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
