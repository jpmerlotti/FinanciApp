<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename tags table
        Schema::rename('tags', 'transaction_categories');

        // 2. Add FK to transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('transaction_category_id')
                ->nullable()
                ->after('amount_cents')
                ->constrained('transaction_categories')
                ->nullOnDelete();
        });

        // 3. Migrate Data (Pivot -> Direct FK)
        // Ensure we pick at least one tag if multiple exist
        $transactionTags = DB::table('transaction_tags')->get(); // Fetches all pivot rows

        foreach ($transactionTags as $pivot) {
             // We just update the transaction to point to this tag (category).
             // If a transaction has multiple tags, this loop will overwrite the ID multiple times,
             // effectively keeping the *last* one processed. Valid for "Migration to 1-N".
            DB::table('transactions')
                ->where('id', $pivot->transaction_id)
                ->update(['transaction_category_id' => $pivot->tag_id]);
        }

        // 4. Drop Pivot Table
        Schema::dropIfExists('transaction_tags');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Revert FK
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['transaction_category_id']);
            $table->dropColumn('transaction_category_id');
        });

        // 2. Rename back
        Schema::rename('transaction_categories', 'tags');

        // 3. Recreate Pivot
        Schema::create('transaction_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->timestamps();
        });
        
        // Data restoration (Approximate - we lost multi-tags, can't restore perfectly)
    }
};
