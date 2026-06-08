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
    Schema::create('stripe_transactions', function (Blueprint $table) {
        $table->id();

        $table->string('stripe_id')->unique();

        $table->timestamp('transaction_date')->nullable();

        $table->string('type')->nullable();

        $table->string('reporting_category')->nullable();

        $table->string('currency', 10)->nullable();

        $table->decimal('gross', 12, 2)->nullable();

        $table->decimal('fee', 12, 2)->nullable();

        $table->decimal('net', 12, 2)->nullable();

        $table->string('status')->nullable();

        $table->text('description')->nullable();

        $table->string('source')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_transactions');
    }
};
