<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('woo_commerce_orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('woo_order_id')->unique();
            $table->string('order_number')->nullable();

            $table->string('status')->nullable();
            $table->timestamp('date_created')->nullable();
            $table->timestamp('date_paid')->nullable();

            $table->string('currency', 10)->nullable();

            $table->decimal('discount_total', 12, 2)->nullable();
            $table->decimal('shipping_total', 12, 2)->nullable();
            $table->decimal('tax_total', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();

            $table->string('payment_method')->nullable();
            $table->string('payment_method_title')->nullable();
            $table->string('transaction_id')->nullable();

            $table->string('customer_id')->nullable();
            $table->string('customer_email')->nullable();

            $table->string('billing_first_name')->nullable();
            $table->string('billing_last_name')->nullable();
            $table->string('billing_company')->nullable();
            $table->string('billing_address_1')->nullable();
            $table->string('billing_address_2')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_postcode')->nullable();
            $table->string('billing_country')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('billing_phone')->nullable();

            $table->string('shipping_first_name')->nullable();
            $table->string('shipping_last_name')->nullable();
            $table->string('shipping_company')->nullable();
            $table->string('shipping_address_1')->nullable();
            $table->string('shipping_address_2')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_postcode')->nullable();
            $table->string('shipping_country')->nullable();

            $table->unsignedBigInteger('matched_stripe_transaction_id')->nullable();
            $table->string('match_status')->nullable();
            $table->string('match_method')->nullable();
            $table->string('match_confidence')->nullable();
            $table->text('match_notes')->nullable();

            $table->json('meta_data')->nullable();
            $table->json('raw_order')->nullable();

            $table->timestamps();

            $table->index('transaction_id');
            $table->index('customer_email');
            $table->index('billing_email');
            $table->index('date_created');
            $table->index('date_paid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('woo_commerce_orders');
    }
};