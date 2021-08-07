<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // Possible transaction types are wallets and credit cards.
            $table->tinyInteger('type');
            // If transaction is made to another wallet, associtate it with a user ID.
            $table->unsignedBigInteger('user_id');
            // Link transactions together to mark them both as fraudulent.
            $table->unsignedBigInteger('parent_id')->nullable();
            // Sender wallet uniqid or credit card number.
            $table->string('from');
            // Receiver wallet uniqid or credit card number.
            $table->string('to');
            // Amount to transfer.
            $table->decimal('amount', 15, 2);
            // Possibility to mark transaction as fraudulent.
            $table->tinyInteger('is_fraudulent')->default(0);
            $table->timestamps();
            // Transactions are not arhived, so there are no soft deletes.

            // Add indexes.
            $table->index('from');
            $table->index('to');
            $table->index('parent_id');

            // Make reference to users table. If user is deleted, delete all his transactions.
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
        Schema::dropIfExists('transactions');
    }
}
