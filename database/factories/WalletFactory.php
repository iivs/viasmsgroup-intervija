<?php

namespace Database\Factories;

use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Wallet::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            // Create a random wallet name and remove dot at the end.
            'name' => substr($this->faker->text(rand(10, 30)), 0, -1),
            // Generate a unique wallet ID.
            'uniqid' => strtoupper(uniqid()),
            // Give random amount of money.
            'balance' => rand(10, 100000)
        ];
    }
}
