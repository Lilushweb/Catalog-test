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
            'category_id' => $categories[2]->id,#DataSourceSettings#
#LocalDataSource: db (docker-compose)
#BEGIN#
<data-source source="LOCAL" name="db (docker-compose)" uuid="948d6b7b-f52b-4009-b7d6-4cae3eb7f269"><database-info product="PostgreSQL" version="15.17 (Debian 15.17-1.pgdg13+1)" jdbc-version="4.2" driver-name="PostgreSQL JDBC Driver" driver-version="42.7.3" dbms="POSTGRES" exact-version="15.17" exact-driver-version="42.7"><identifier-quote-string>&quot;</identifier-quote-string></database-info><case-sensitivity plain-identifiers="lower" quoted-identifiers="exact"/><driver-ref>postgresql</driver-ref><jdbc-driver>org.postgresql.Driver</jdbc-driver><jdbc-url>jdbc:postgresql://localhost:5433/building</jdbc-url><secret-storage>master_key</secret-storage><user-name>admin</user-name><schema-mapping><introspection-scope><node kind="database" qname="@"><node kind="schema" qname="@"/></node></introspection-scope></schema-mapping><introspection-level>3</introspection-level><working-dir>$ProjectFileDir$</working-dir></data-source>
#END#


        ]);
    }
}
