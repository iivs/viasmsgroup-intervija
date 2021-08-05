<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            // Each wallet must belong to a user.
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            // Randomly generated wallet unique ID.
            $table->string('uniqid');
            // Maybe some is a trillionare. Add 10€ as a bonus for registration.
            $table->decimal('balance', 15, 2)->default(10);
            $table->timestamps();
            // Prevent user from deleting a wallet accidentally. There is a way to return it without losing all money.
            $table->softDeletes();

            // Create reference.
            $table
                ->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallets');
    }
}
