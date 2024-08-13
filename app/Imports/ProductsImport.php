<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Image;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;

class ProductsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    private $restoredNames = [];
    private $updatedNames = [];

    public function model(array $row)
    {
        $category = Category::findOrFail($row['category_id']);
        $product = Product::withTrashed()->where('name', $row['name'])->first();

        if ($product) {
            if ($product->trashed()) {
                $product->restore();
                $this->restoredNames[] = $row['name'];
            } else {
                $this->updatedNames[] = $row['name'];
            }

            $product->update([
                'description' => $row['description'],
                'price' => $row['price'],
                'stock' => $row['stock'],
                'category_id' => $category->id,
            ]);
        } else {
            $product = new Product([
                'name' => $row['name'],
                'description' => $row['description'],
                'price' => $row['price'],
                'stock' => $row['stock'],
                'category_id' => $category->id,
            ]);
            $product->save();
        }

        if (!empty($row['image'])) {
            $this->handleImageImport($product, $row['image']);
        }

        return $product;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|string',
        ];
    }

    private function handleImageImport(Product $product, string $imageData)
    {
        $imageUrls = explode(',', $imageData);
        foreach ($imageUrls as $imageUrl) {
            $imageUrl = trim($imageUrl);
            if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                $this->importFromUrl($product, $imageUrl);
            } elseif (Str::startsWith($imageUrl, 'data:image')) {
                $this->importFromBase64($product, $imageUrl);
            } elseif (file_exists($imageUrl)) {
                $this->importFromLocalPath($product, $imageUrl);
            }
        }
    }

    private function importFromUrl(Product $product, string $url)
    {
        $response = Http::get($url);
        if ($response->successful()) {
            $imageContent = $response->body();
            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            $this->saveImage($product, $imageContent, $extension);
        }
    }

    private function importFromBase64(Product $product, string $base64String)
    {
        $imageContent = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64String));
        $extension = explode('/', mime_content_type($base64String))[1];
        $this->saveImage($product, $imageContent, $extension);
    }

    private function importFromLocalPath(Product $product, string $path)
    {
        if (file_exists($path)) {
            $imageContent = file_get_contents($path);
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $this->saveImage($product, $imageContent, $extension);
        }
    }

    private function saveImage(Product $product, $imageContent, $extension)
    {
        $imageName = 'product_' . $product->id . '_' . time() . '_' . Str::random(5) . '.' . $extension;
        $path = 'product_images/' . $imageName;

        Storage::disk('public')->put($path, $imageContent);

        Image::create([
            'product_id' => $product->id,
            'path' => $path
        ]);
    }

    public function getRestoredNames()
    {
        return $this->restoredNames;
    }

    public function getUpdatedNames()
    {
        return $this->updatedNames;
    }
}
