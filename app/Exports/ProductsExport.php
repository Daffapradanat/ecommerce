<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

class ProductsExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithMapping, WithCustomValueBinder
{
    public function collection()
    {
        return Product::with('image')->get();
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Description',
            'Price',
            'Stock',
            'Category ID',
            'Image',
        ];
    }

    public function map($product): array
    {
        $imageUrls = $product->image->map(function($image) {
            return asset('storage/' . $image->path);
        })->implode(', ');

        return [
            $product->name,
            $product->description,
            $product->price,
            $product->stock,
            $product->category_id,
            $imageUrls ?: '',
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if (strpos($cell->getColumn(), 'F') === 0 && $value !== '') {
            $urls = explode(', ', $value);
            $cell->setValueExplicit(implode(", ", $urls), DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
