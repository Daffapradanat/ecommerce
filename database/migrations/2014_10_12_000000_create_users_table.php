<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        DB::table('roles')->insert([
            [
                'name' => 'superadmin',
                'permissions' => json_encode([
                    'products',
                    'products.create',
                    'products.edit',
                    'products.delete',
                    'orders',
                    'categories',
                    'categories.create',
                    'categories.edit',
                    'categories.delete',
                    'roles',
                    'roles.create',
                    'roles.edit',
                    'roles.delete',
                    'users',
                    'users.create',
                    'users.edit',
                    'users.delete',
                    'buyers',
                    'buyers.create',
                    'buyers.edit',
                    'buyers.delete'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'admin',
                'permissions' => json_encode([
                    'products',
                    'products.create',
                    'products.edit',
                    'products.delete',
                    'orders',
                    'categories',
                    'categories.create',
                    'categories.edit',
                    'categories.delete',
                    'roles',
                    'roles.create',
                    'roles.edit',
                    'roles.delete',
                    'buyers',
                    'buyers.create',
                    'buyers.edit',
                    'buyers.delete'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'user',
                'permissions' => json_encode([
                    'products',
                    'products.create',
                    'products.edit',
                    'products.delete',
                    'orders',
                    'categories',
                    'categories.create',
                    'categories.edit',
                    'categories.delete'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);

        $userRoleId = DB::table('roles')->where('name', 'user')->value('id');

        Schema::create('users', function (Blueprint $table) use ($userRoleId) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->foreignId('role_id')->constrained('roles')->default($userRoleId);
            $table->string('email')->unique();
            $table->string('new_email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('verification_code')->nullable();
            $table->string('email_change_verification_code')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
};
