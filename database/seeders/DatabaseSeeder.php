<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            // UsersTableSeeder::class,
            CategoriesTableSeeder::class,
<<<<<<< HEAD
            ProductsTableSeeder::class,
            BuyersTableSeeder::class,
=======
            // ProductsTableSeeder::class,
            // BuyersTableSeeder::class,
>>>>>>> 9e59e9efe56e52d879af0fb2232e489f79c8d300
        ]);
    }
}
