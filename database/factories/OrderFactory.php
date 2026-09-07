<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'customer_name' => $this->faker->name(),
            'status' => 'pending',
            'total_amount' => $this->faker->randomFloat(2, 50, 2000),
        ];
    }
}
