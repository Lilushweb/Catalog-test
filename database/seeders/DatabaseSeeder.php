<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $categories = collect([
            [
                'name' => 'Ноутбуки',
                'description' => 'Портативные устройства для работы, учебы и развлечений.',
            ],
            [
                'name' => 'Смартфоны',
                'description' => 'Современные смартфоны для связи, фото и приложений.',
            ],
            [
                'name' => 'Аксессуары',
                'description' => 'Полезные дополнения для техники и повседневного использования.',
            ],
        ])->map(fn (array $category) => Category::query()->firstOrCreate(
            ['name' => $category['name']],
            ['description' => $category['description']]
        ));

        User::query()->updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin User',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        if (Product::query()->exists()) {
            return;
        }

        Product::factory()->count(18)->create([
            'category_id' => $categories[0]->id,
        ]);

        Product::factory()->count(12)->create([
            'category_id' => $categories[1]->id,
        ]);

        Product::factory()->count(8)->create([
            'category_id' => $categories[2]->id,
        ]);
    }
}
