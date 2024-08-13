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
            $this->processBase64Image($row['image'], $product);
        }

        return $product;
    }

    private function processBase64Image($base64String, $product)
    {
        try {
            $imageData = base64_decode($base64String);
            $fileName = $product->id . '_' . uniqid() . '.png';
            $path = 'product_images/' . $fileName;

            Storage::disk('public')->put($path, $imageData);

            Image::create([
                'product_id' => $product->id,
                'path' => $path,
            ]);

            Log::info("Image processed successfully for product: " . $product->id);
        } catch (\Exception $e) {
            Log::error("Failed to process image for product: " . $product->id . ". Error: " . $e->getMessage());
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
