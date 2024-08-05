<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class BuyersTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        for ($i = 0; $i < 50; $i++) {
            DB::table('buyers')->insert([
                'name' => $faker->name,
                'image' => $faker->imageUrl(200, 200, 'people'),
                'email' => $faker->unique()->safeEmail,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => $faker->sha1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
