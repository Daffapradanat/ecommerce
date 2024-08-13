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
        $this->existingProducts = Product::withTrashed()->pluck('name', 'id')->toArray();
    }

    public function model(array $row)
    {
        $this->rowCount++;

        $existingProduct = Product::withTrashed()->where('name', $row['name'])->first();

        if ($existingProduct) {
            if ($existingProduct->trashed()) {
                $existingProduct->restore();
                $existingProduct->update([
                    'description' => $row['description'],
                    'price' => $row['price'],
                    'stock' => $row['stock'],
                    'category_id' => $row['category_id'],
                ]);
                $product = $existingProduct;
            } else {
                $failureMessage = "The product \"{$row['name']}\" already exists and is not deleted.";
                $failure = new Failure(
                    $this->rowCount,
                    'name',
                    [$failureMessage],
                    $row
                );
                $this->onFailure($failure);
                return null;
            }
        } else {
            $product = Product::create([
                'name' => $row['name'],
                'description' => $row['description'],
                'price' => $row['price'],
                'stock' => $row['stock'],
                'category_id' => $row['category_id'],
            ]);
        }

        if (!empty($row['image'])) {
            $imagePaths = explode(',', $row['image']);
            foreach ($imagePaths as $imagePath) {
                $imagePath = trim($imagePath);
                Log::info("Processing image: $imagePath");
                $newPath = $this->processImage($imagePath, $product->id);
                if ($newPath) {
                    Log::info("Image processed successfully: $newPath");
                    Image::create([
                        'product_id' => $product->id,
                        'path' => $newPath,
                    ]);
                } else {
                    Log::error("Failed to process image: $imagePath");
                }
            }
        }

        return $product;
    }

    private function processImage($imagePath, $productId)
    {
        $storageFolder = 'product_images';
        $fileName = $productId . '_' . uniqid() . '_' . basename($imagePath);
        $newPath = $storageFolder . '/' . $fileName;

        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $contents = @file_get_contents($imagePath);
            if ($contents === false) {
                Log::error("Failed to download image from URL: $imagePath");
                return null;
            }
            Storage::disk('public')->put($newPath, $contents);
            return $newPath;
        } else {
            $fullPath = realpath($imagePath);
            if (!$fullPath || !file_exists($fullPath)) {
                Log::error("Local file not found: $imagePath");
                return null;
            }

            $contents = @file_get_contents($fullPath);
            if ($contents === false) {
                Log::error("Failed to read local file: $fullPath");
                return null;
            }

            Storage::disk('public')->put($newPath, $contents);
            return $newPath;
        }
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
