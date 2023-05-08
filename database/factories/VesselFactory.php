<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VesselFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->words($nb = 2, $maxNbChars = 12)
        ];
    }
}
