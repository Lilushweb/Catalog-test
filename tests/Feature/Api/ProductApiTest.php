<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_index_returns_paginated_products_with_categories(): void
    {
        $category = Category::factory()->create();

        Product::factory()->count(13)->create([
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/products');

        $response
            ->assertOk()
            ->assertJsonPath('meta.per_page', 12)
            ->assertJsonCount(12, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'price',
                        'category_id',
                        'category' => ['id', 'name', 'description'],
                    ],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_authenticated_user_can_create_product_via_api(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $category = Category::factory()->create();

        $response = $this->postJson('/api/products', [
            'name' => 'Mechanical Keyboard',
            'description' => 'Compact layout with tactile switches.',
            'price' => 199.99,
            'category_id' => $category->id,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Mechanical Keyboard')
            ->assertJsonPath('data.category.id', $category->id);

        $this->assertDatabaseHas('products', [
            'name' => 'Mechanical Keyboard',
            'category_id' => $category->id,
        ]);
    }

    public function test_guest_cannot_create_product_via_api(): void
    {
        $category = Category::factory()->create();

        $this->postJson('/api/products', [
            'name' => 'Unauthorized product',
            'price' => 10,
            'category_id' => $category->id,
        ])->assertUnauthorized();
    }

    public function test_login_endpoint_returns_sanctum_token_for_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email'],
            ]);
    }
}
