<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
    $table->id();

    $table->foreignId('sender_account_id')->constrained('accounts');
    $table->foreignId('receiver_account_id')->constrained('accounts');

    $table->decimal('amount', 12, 2);

    $table->enum('type', ['debit', 'credit']);

    $table->string('description')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
