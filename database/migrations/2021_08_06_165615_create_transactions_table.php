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
            // Sender wallet uniqid or credit card number.
            $table->string('from');
            // Receiver wallet uniqid or credit card number.
            $table->string('to');
            // Amount to transfer.
            $table->decimal('amount', 15, 2);
            // Possibility to mark transaction as fraudulent.
            $table->tinyInteger('is_fraudulent')->default(0);
            // Marks for temporary or permanent deletion.
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();

            // Add indexes.
            $table->index('from');
            $table->index('to');
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
