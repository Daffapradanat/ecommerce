<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Image;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\Http;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    private $rowCount = 0;

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
            $this->processImages($row['image'], $product);
        }

        return $product;
    }

    private function processImages($imageString, $product)
    {
        $images = explode(',', $imageString);
        foreach ($images as $image) {
            $image = trim($image);
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                $this->processUrlImage($image, $product);
            } else {
                $this->processLocalImage($image, $product);
            }
        }
    }

    private function processUrlImage($url, $product)
    {
        try {
            $response = Http::get($url);
            if ($response->successful()) {
                $imageData = $response->body();
                $fileName = $product->id . '_' . uniqid() . '.jpg';
                $path = 'product_images/' . $fileName;

                Storage::disk('public')->put($path, $imageData);

                Image::create([
                    'product_id' => $product->id,
                    'path' => $path,
                ]);

                Log::info("URL image processed successfully for product: " . $product->id);
            } else {
                Log::error("Failed to fetch image from URL for product: " . $product->id . ". URL: " . $url);
            }
        } catch (\Exception $e) {
            Log::error("Failed to process URL image for product: " . $product->id . ". Error: " . $e->getMessage());
        }
    }

    private function processLocalImage($filePath, $product)
    {
        try {
            $importFolder = storage_path('app/imports');
            $fullPath = str_replace('\\', '/', $filePath);
            $fullPath = $importFolder . '/' . basename($fullPath);

            if (file_exists($fullPath)) {
                $fileName = $product->id . '_' . uniqid() . '.' . pathinfo($fullPath, PATHINFO_EXTENSION);
                $path = 'product_images/' . $fileName;

                Storage::disk('public')->put($path, file_get_contents($fullPath));

                Image::create([
                    'product_id' => $product->id,
                    'path' => $path,
                ]);

                Log::info("Local image processed successfully for product: " . $product->id);
            } else {
                Log::error("Local image file not found for product: " . $product->id . ". Path: " . $filePath);
            }
        } catch (\Exception $e) {
            Log::error("Failed to process local image for product: " . $product->id . ". Error: " . $e->getMessage());
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
}
