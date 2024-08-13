<?php

namespace App\Imports;

use App\Models\Image;
use App\Models\Product;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;

class ProductsImport
{
    public $errors = [];

    public function import($filePath)
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        array_shift($rows);

        foreach ($rows as $rowIndex => $row) {
            $this->processRow($row, $rowIndex + 2);
        }
    }

    private function processRow($row, $rowNumber)
    {
        try {
            $name = trim($row[0]);
            \Log::info("Processing row {$rowNumber}: " . json_encode($row));

            if (empty($name)) {
                throw new \Exception("The name field is required.");
            }

            $description = $row[1];
            $price = $row[2];
            $stock = $row[3];
            $category_id = $row[4];
            $imagePaths = $row[5];

            $existingProduct = Product::withTrashed()->where('name', $name)->first();

            if ($existingProduct) {
                if ($existingProduct->trashed()) {
                    $existingProduct->restore();
                    $existingProduct->update([
                        'description' => $description,
                        'price' => $price,
                        'stock' => $stock,
                        'category_id' => $category_id,
                    ]);
                    $product = $existingProduct;
                } else {
                    throw new \Exception("The product \"{$name}\" already exists and is not deleted.");
                }
            } else {
                $product = Product::create([
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'stock' => $stock,
                    'category_id' => $category_id,
                ]);
            }

            $this->processImages($imagePaths, $product);

        } catch (\Exception $e) {
            $this->errors[] = "Row {$rowNumber}: {$e->getMessage()}";
            \Log::error("Error processing row {$rowNumber}: {$e->getMessage()}");
        }
    }

    private function processImages($imagePaths, $product)
    {
        if (empty($imagePaths)) {
            return;
        }

        $storageFolder = 'product_images';
        $paths = explode(',', $imagePaths);

        foreach ($paths as $imagePath) {
            $imagePath = trim($imagePath);
            $fileName = $product->id.'_'.uniqid().'_'.basename($imagePath);
            $newPath = $storageFolder.'/'.$fileName;

            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                $imageContents = @file_get_contents($imagePath);
                if ($imageContents !== false) {
                    Storage::disk('public')->put($newPath, $imageContents);
                    Image::create([
                        'product_id' => $product->id,
                        'path' => $newPath,
                    ]);
                } else {
                    $this->errors[] = "Failed to download image from URL: {$imagePath}";
                }
            } elseif (file_exists($imagePath)) {
                Storage::disk('public')->putFileAs($storageFolder, new \Illuminate\Http\File($imagePath), $fileName);
                Image::create([
                    'product_id' => $product->id,
                    'path' => $newPath,
                ]);
            } else {
                $this->errors[] = "Invalid image path or URL: {$imagePath}";
            }
        }
    }
}

// namespace App\Imports;

// use App\Models\Image;
// use App\Models\Product;
// use Illuminate\Support\Facades\Storage;
// use Maatwebsite\Excel\Concerns\Importable;
// use Maatwebsite\Excel\Concerns\SkipsFailures;
// use Maatwebsite\Excel\Concerns\SkipsOnFailure;
// use Maatwebsite\Excel\Concerns\ToModel;
// use Maatwebsite\Excel\Concerns\WithHeadingRow;
// use Maatwebsite\Excel\Concerns\WithValidation;

// class ProductsImport implements SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
// {
//     use Importable, SkipsFailures;

//     private $rowCount = 0;
//     public $errors = [];

//     public function model(array $row)
//     {
//         $this->rowCount++;

//         try {
//             $existingProduct = Product::withTrashed()->where('name', $row['name'])->first();

//             if ($existingProduct) {
//                 if ($existingProduct->trashed()) {
//                     $existingProduct->restore();
//                     $existingProduct->update([
//                         'description' => $row['description'],
//                         'price' => $row['price'],
//                         'stock' => $row['stock'],
//                         'category_id' => $row['category_id'],
//                     ]);
//                     $product = $existingProduct;
//                 } else {
//                     throw new \Exception("The product \"{$row['name']}\" already exists and is not deleted.");
//                 }

//                 if (empty($row['name']) && empty($row['description']) && empty($row['price']) && empty($row['stock']) && empty($row['category_id'])) {
//                     return null;
//                 }
//             } else {
//                 $product = Product::create([
//                     'name' => $row['name'],
//                     'description' => $row['description'],
//                     'price' => $row['price'],
//                     'stock' => $row['stock'],
//                     'category_id' => $row['category_id'],
//                 ]);
//             }

//             $this->processImages($row, $product);

//             return $product;
//         } catch (\Exception $e) {
//             $this->errors[] = "Row {$this->rowCount}: {$e->getMessage()}";
//             return null;
//         }
//     }

//     private function processImages($row, $product)
//     {
//         if (!empty($row['image'])) {
//             $imagePaths = explode(',', $row['image']);
//             foreach ($imagePaths as $imagePath) {
//                 $imagePath = trim($imagePath);
//                 $newPath = $this->processImage($imagePath, $product->id);
//                 if ($newPath) {
//                     Image::create([
//                         'product_id' => $product->id,
//                         'path' => $newPath,
//                     ]);
//                 }
//             }
//         }
//     }

//     private function processImage($imagePath, $productId)
//     {
//         $storageFolder = 'product_images';
//         $fileName = $productId.'_'.uniqid().'_'.basename($imagePath);
//         $newPath = $storageFolder.'/'.$fileName;

//         if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
//             $imageContents = @file_get_contents($imagePath);
//             if ($imageContents !== false) {
//                 Storage::disk('public')->put($newPath, $imageContents);
//                 return $newPath;
//             }
//         } elseif (file_exists($imagePath)) {
//             $fileContents = file_get_contents($imagePath);
//             Storage::disk('public')->put($newPath, $fileContents);
//             return $newPath;
//         }

//         $this->errors[] = "Failed to process image: {$imagePath}";
//         return null;
//     }

//     public function rules(): array
//     {
//         return [
//             'name' => 'required|string|max:255',
//             'description' => 'required|string',
//             'price' => 'required|numeric|min:0',
//             'stock' => 'required|integer|min:0',
//             'category_id' => 'required|exists:categories,id',
//             'image' => 'nullable|string',
//         ];
//     }

//     public function customValidationMessages()
//     {
//         return [
//             'name.required' => 'The name field is required.',
//             'description.required' => 'The description field is required.',
//             'price.required' => 'The price field is required.',
//             'price.numeric' => 'The price must be a number.',
//             'stock.required' => 'The stock field is required.',
//             'stock.integer' => 'The stock must be an integer.',
//             'category_id.required' => 'The category_id field is required.',
//             'category_id.exists' => 'The selected category_id is invalid.',
//         ];
//     }
// }
