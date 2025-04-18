<?php

namespace Database\Factories;

use App\Models\Row;
use Illuminate\Database\Eloquent\Factories\Factory;

class RowFactory extends Factory
{
    protected $model = Row::class;

    public function definition()
    {
        return [
            'excel_id' => $this->faker->unique()->randomNumber(5),
            'name' => $this->faker->name,
            'date' => $this->faker->date(),
        ];
    }
}
