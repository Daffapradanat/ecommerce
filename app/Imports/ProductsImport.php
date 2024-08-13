<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Image;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Validators\Failure;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
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

        $fullProductName = "Product: \"{$row['name']}\", Price: Rp {$row['price']}, Stock: {$row['stock']}, Category ID: {$row['category_id']}";

        if (in_array($row['name'], $this->existingProducts)) {
            $failureMessage = "The product \"{$row['name']}\" with Price: Rp {$row['price']}, Stock: {$row['stock']}, Category ID: {$row['category_id']} already exists.";
            $failure = new Failure(
                $this->rowCount,
                'name',
                [$failureMessage],
                $row
            );
            $this->onFailure($failure);
            return null;
        }

        $this->existingProducts[] = $row['name'];

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
        $storageFolder = storage_path('app/public/product_images');

        if (!File::isDirectory($storageFolder)) {
            File::makeDirectory($storageFolder, 0755, true, true);
        }

        if (File::exists($imagePath)) {
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
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
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
}
