<?php

namespace Database\Factories;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Model::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $types = Config::get('transactions.type');
        $fraudulent_values = Config::get('transactions.is_fraudulent');

        return [
            // Random transaction type. Either wallet or credit card.
            'type' => $types[array_rand($types)],
            // Randomly mark transactions as fraudulent.
            'is_fraudulent' => $fraudulent_values[array_rand($fraudulent_values)]
            // Other values may depend on how much money the user has in his wallet.
        ];
    }
}
