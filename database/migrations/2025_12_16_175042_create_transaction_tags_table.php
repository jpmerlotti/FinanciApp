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
        Schema::create('transaction_tags', function (Blueprint $table) {
            // $table->id(); removed due to composite PK
            $table->foreignId('transaction_id')->unsigned(); // No constraint to allow standalone install compatibility
            $table->unsignedBigInteger('tag_id'); // No FK constraint

            $table->primary(['transaction_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_tags');
    }
};
