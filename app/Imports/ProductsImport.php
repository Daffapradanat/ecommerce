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
        $storageFolder = 'product_images';
        $fileName = $productId . '_' . uniqid() . '_' . basename($imagePath);
        $newPath = $storageFolder . '/' . $fileName;

        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $tempImage = tempnam(sys_get_temp_dir(), 'img');
            copy($imagePath, $tempImage);
            $file = new \Illuminate\Http\UploadedFile($tempImage, $fileName);
        } elseif (File::exists($imagePath)) {
            $file = new \Illuminate\Http\UploadedFile($imagePath, basename($imagePath));
        } else {
            return null;
        }

        $path = $file->store($storageFolder, 'public');

        if (isset($tempImage) && file_exists($tempImage)) {
            unlink($tempImage);
        }

        return $path;
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
