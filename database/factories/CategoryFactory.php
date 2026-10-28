<?php

namespace Database\Factories;

use App\Domain\Catalog\Models\Category as ModelsCategory;
use App\Models\Domain\Catalog\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = ModelsCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // unique() evita colisiones del constraint ['organization_id', 'name']
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->optional(0.7)->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
