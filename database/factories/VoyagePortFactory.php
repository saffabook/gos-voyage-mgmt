<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VoyagePortFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(17),
            'description' => $this->faker->sentence(30),
            'directions' => $this->faker->sentence(25),
            'addressId' => 42
        ];
    }
}
