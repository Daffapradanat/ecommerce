<?php

namespace App\Exports;


use App\Models\Product;
use App\Models\Image;
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
            'name',
            'description',
            'price',
            'stock',
            'category_id',
            'image',
        ];
    }

    public function map($product): array
    {
        $imageUrls = $product->images->pluck('path')->map(function($path) {
            return asset('storage/' . $path);
        })->implode(', ');

        return [
            $product->name,
            $product->description,
            $product->price,
            $product->stock,
            $product->category_id,
            $imageUrls,
        ];
    }

    public function bindValue(Cell $cell, $value)
    {
        if (strpos($cell->getColumn(), 'F') === 0 && $value !== '') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }
}
