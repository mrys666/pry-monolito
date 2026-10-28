<?php

namespace Database\Seeders;

use App\Domain\Access\Enums\UserRole;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Organization\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Creamos la primera organización
        $techStore = Organization::create(['name' => 'Tech Store S.A.']);

        // Usuarios para Tech Store
        $adminTech = User::create([
            'organization_id' => $techStore->id,
            'name' => 'Admin Tech',
            'email' => 'admin@tech.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);

        $viewerTech = User::create([
            'organization_id' => $techStore->id,
            'name' => 'Viewer Tech',
            'email' => 'viewer@tech.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Viewer,
        ]);

        // Categorías y Productos para Tech Store
        $catLaptops = Category::create([
            'organization_id' => $techStore->id,
            'name' => 'Laptops',
            'description' => 'Computadoras portátiles',
            'is_active' => true,
        ]);

        $catAccesorios = Category::create([
            'organization_id' => $techStore->id,
            'name' => 'Accesorios',
            'description' => 'Periféricos y más',
            'is_active' => true,
        ]);

        // Producto normal (Stock alto)
        Product::create([
            'organization_id' => $techStore->id,
            'category_id' => $catLaptops->id,
            'sku' => 'LAP-001',
            'name' => 'MacBook Pro 14"',
            'price' => 1999.99,
            'stock' => 15,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);

        // Producto con stock bajo (Para disparar alertas)
        Product::create([
            'organization_id' => $techStore->id,
            'category_id' => $catAccesorios->id,
            'sku' => 'ACC-001',
            'name' => 'Mouse Inalámbrico Logitech',
            'price' => 49.99,
            'stock' => 2, // Menor al mínimo
            'minimum_stock' => 10,
            'is_active' => true,
        ]);

        // 2. Creamos la segunda organización (Para probar aislamiento)
        $officePlus = Organization::create(['name' => 'Office Plus']);

        User::create([
            'organization_id' => $officePlus->id,
            'name' => 'Admin Office',
            'email' => 'admin@office.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);

        $catMuebles = Category::create([
            'organization_id' => $officePlus->id,
            'name' => 'Muebles',
            'description' => 'Mobiliario de oficina',
            'is_active' => true,
        ]);

        Product::create([
            'organization_id' => $officePlus->id,
            'category_id' => $catMuebles->id,
            'sku' => 'MUE-001',
            'name' => 'Silla Ergonómica',
            'price' => 150.00,
            'stock' => 30,
            'minimum_stock' => 5,
            'is_active' => true,
        ]);
    }
}
