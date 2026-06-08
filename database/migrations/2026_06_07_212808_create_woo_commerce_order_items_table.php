<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('woo_commerce_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('woo_commerce_order_id')
                ->constrained('woo_commerce_orders')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('woo_order_id')->index();
            $table->unsignedBigInteger('woo_line_item_id')->nullable()->index();

            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('variation_id')->nullable()->index();

            $table->string('name')->nullable();
            $table->string('sku')->nullable();

            $table->integer('quantity')->default(0);

            $table->decimal('subtotal', 12, 2)->nullable();
            $table->decimal('subtotal_tax', 12, 2)->nullable();
            $table->decimal('total', 12, 2)->nullable();
            $table->decimal('total_tax', 12, 2)->nullable();

            $table->json('taxes')->nullable();
            $table->json('meta_data')->nullable();
            $table->json('raw_item')->nullable();

            $table->timestamps();

            $table->unique(['woo_order_id', 'woo_line_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('woo_commerce_order_items');
    }
};