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
        Schema::table('counterparties', function (Blueprint $table) {
            $table->string('document')->nullable();
            $table->string('email')->nullable();
            $table->string('company_name')->nullable();
            $table->string('mobile_phone')->nullable();
            $table->string('phone')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('street')->nullable();
            $table->string('number')->nullable();
            $table->string('complement')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('municipal_registration')->nullable();
            $table->string('state_registration')->nullable();
            $table->boolean('should_send_boleto')->default(false);
            $table->json('additional_emails')->nullable();
            $table->text('observations')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counterparties', function (Blueprint $table) {
            $table->dropColumn([
                'document',
                'email',
                'company_name',
                'mobile_phone',
                'phone',
                'zip_code',
                'street',
                'number',
                'complement',
                'district',
                'city',
                'state',
                'municipal_registration',
                'state_registration',
                'should_send_boleto',
                'additional_emails',
                'observations',
            ]);
        });
    }
};
