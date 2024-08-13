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

class ProductsImport implements SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use Importable, SkipsFailures;

    private $existingProducts = [];

    private $rowCount = 0;

    public function __construct()
    {
        $this->existingProducts = Product::pluck('name')->toArray();
    }

    private function processImage($imagePath, $productId)
    {
        $storageFolder = storage_path('app/public/product_images');

        if (! File::isDirectory($storageFolder)) {
            File::makeDirectory($storageFolder, 0755, true, true);
        }

        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $fileName = $productId.'_'.uniqid().'_'.basename($imagePath);
            $newPath = $storageFolder.'/'.$fileName;

            $imageContent = file_get_contents($imagePath);
            if ($imageContent !== false) {
                File::put($newPath, $imageContent);

                return 'product_images/'.$fileName;
            }
        } else {
            if (File::exists($imagePath)) {
                $fileName = $productId.'_'.uniqid().'_'.basename($imagePath);
                $newPath = $storageFolder.'/'.$fileName;
                File::copy($imagePath, $newPath);

                return 'product_images/'.$fileName;
            }
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
