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
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|string',
        ];
    }

    private function handleImageImport(Product $product, string $imageData)
    {
        if (filter_var($imageData, FILTER_VALIDATE_URL)) {
            $response = Http::get($imageData);
            if ($response->successful()) {
                $imageContent = $response->body();
                $extension = pathinfo(parse_url($imageData, PHP_URL_PATH), PATHINFO_EXTENSION);
            } else {
                return;
            }
        } elseif (Str::startsWith($imageData, 'data:image')) {
            $imageContent = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));
            $extension = explode('/', mime_content_type($imageData))[1];
        } else {
            return;
        }

        $imageName = 'product_' . $product->id . '_' . time() . '.' . $extension;
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
