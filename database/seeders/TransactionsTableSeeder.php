<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Wallet;

class TransactionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Transaction::truncate();

        $faker = \Faker\Factory::create();

        // Get all wallets from all users. Doesn't matter if they have money in the wallet.
        $wallets = Wallet::all()->toArray();

        // Try to define 1000 transactions of total.
        for ($i = 0; $i < 1000; $i++) {
            $transaction = Transaction::factory()->definition();

            switch ($transaction['type']) {
                case \Config::get('transactions.type.card'):
                    $key = array_rand($wallets);
                    $wallets[$key]['uniqid'];

                    $transaction += [
                        // Random credit card number with 4x4 digits, but show only last 4 digits.
                        'from' => '****-****-****-'.rand(1000, 9999),
                        // Random existing wallet uniqid.
                        'to' => $wallets[$key]['uniqid'],
                        // Random amount since money is transfered from a credit card.
                        'amount' => rand(10, 100000)
                    ];
                    break;
                case \Config::get('transactions.type.wallet'):
                    // True if is an internal transaction from a wallet to wallet. False of from a wallet to card.
                    if ($faker->boolean(50)) {
                        // Pick a random wallet to transfer money from.
                        $key = array_rand($wallets);
                        $wallet = $wallets[$key];

                        // Make sure destination wallet is not the same as origin.
                        $excluded_wallets = array_values(array_diff_key($wallets, $wallet));
                        $rand = rand(0, count($excluded_wallets) - 1);

                        $transaction += [
                            // Random credit card number with 4x4 digits.
                            'from' => $wallets[$key]['uniqid'],
                            // Random existing wallet uniqid.
                            'to' => $excluded_wallets[$rand]['uniqid'],
                            // Send random amount of money. Otherwise we would need to update the wallet balance.
                            'amount' => rand(10, 100000)
                        ];
                    } else {
                        // Change type to card for outgoing transaction.
                        $transaction['type'] = \Config::get('transactions.type.card');
                        // This is a transaction from a wallet to credit card.
                        $key = array_rand($wallets);
                        $wallet = $wallets[$key];

                        $transaction += [
                            'from' => $wallet['uniqid'],
                            // For external transactions use destination a random credit card number.
                            'to' => '****-****-****-'.rand(1000, 9999),
                            // Send random amount of money. Otherwise we would need to update the wallet balance.
                            'amount' => rand(10, 100000)
                        ];
                    }
                    break;
            }

            Transaction::create($transaction);
        }
    }
}
