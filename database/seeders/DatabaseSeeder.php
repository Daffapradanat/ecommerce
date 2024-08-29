<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            // CategoriesTableSeeder::class,
            // UserSeeder::class,
            ProductsTableSeeder::class,
            BuyersTableSeeder::class,
        ]);
    }
}
