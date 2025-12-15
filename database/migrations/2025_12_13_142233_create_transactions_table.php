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
            $table->string('title')->nullable();
            $table->string('type');
            $table->integer('amount_cents')->default(0);
            $table->date('transaction_date');
            $table->string('payment_proof')->nullable();
            $table->string('status')->default('pending');
            $table->string('recipient')->nullable();
            $table->text('description')->nullable();

            $table->uuid('recurring_group_id')->nullable()->index();
            $table->integer('installment_number')->nullable();
            $table->integer('total_installments')->nullable();

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
