<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reporting_product_group_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reporting_product_group_id')
                ->constrained('reporting_product_groups')
                ->cascadeOnDelete();

            $table->string('match_field');
            $table->string('match_operator')->default('contains');
            $table->string('match_value');

            $table->string('level_name')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->index(['match_field', 'match_operator']);
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reporting_product_group_rules');
    }
};