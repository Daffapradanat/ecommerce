<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoriesTableSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Electronics',
            'Clothing',
            'Books',
            'Home & Garden',
            'Sports & Outdoors',
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'name' => $category,
                'slug' => Str::slug($category),
                'description' => "Description for $category category",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
