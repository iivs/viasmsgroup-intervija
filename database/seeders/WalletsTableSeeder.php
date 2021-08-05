<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Schema;

class WalletsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate existing records from product_attributes table to start from scratch.
        Schema::disableForeignKeyConstraints();
        Wallet::truncate();
        Schema::enableForeignKeyConstraints();

        // Get all users and then generate wallets for them.
        $users = User::all();

        foreach ($users as $user) {
            // Generate random amount of wallets from 1 to 5 of total.
            $wallet_count = rand(1, 5);
            $wallets = [];

            for ($i = 0; $i <= $wallet_count; $i++) {
                $wallets[] = Wallet::factory()->definition();
            }

            // Save user wallets.
            $user->wallets()->createMany($wallets);
        }
    }
}
