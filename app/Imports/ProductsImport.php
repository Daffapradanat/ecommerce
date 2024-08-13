<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;


class ProductsImport implements ToModel, WithHeadingRow, WithValidation
{

    private $duplicateNames = [];

    public function model(array $row)
    {
        $category = Category::findOrFail($row['category_id']);

        $existingProduct = Product::where('name', $row['name'])->first();
        if ($existingProduct) {
            $this->duplicateNames[] = $row['name'];
            return null;
        }

        $product = new Product([
            'name' => $row['name'],
            'description' => $row['description'],
            'price' => $row['price'],
            'stock' => $row['stock'],
            'category_id' => $category->id,
        ]);

        $product->save();

        if (!empty($row['image'])) {
            $this->handleImageImport($product, $row['image']);
        }

        return $product;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name'),
            ],
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
            $imageContent = file_get_contents($imageData);
            $extension = pathinfo(parse_url($imageData, PHP_URL_PATH), PATHINFO_EXTENSION);
        } elseif (Str::startsWith($imageData, 'data:image')) {
            $imageContent = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));
            $extension = explode('/', mime_content_type($imageData))[1];
        } else {
            return;
        }

        $imageName = 'product_' . $product->id . '_' . time() . '.' . $extension;
        $path = 'product_images/' . $imageName;

        Storage::disk('public')->put($path, $imageContent);

        $product->image()->create(['path' => $path]);
    }
}
