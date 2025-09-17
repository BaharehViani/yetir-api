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
            $table->ulid('id')->primary();
            $table->foreignUlid('invoice_id')->constrained();
            $table->unsignedBigInteger('amount');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('gateway');
            $table->string('transaction_id')->index();
            $table->string('ref_id')->nullable()->index();
            $table->ipAddress('ip')->nullable();
            $table->timestamp('paid_at')->nullable();
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
