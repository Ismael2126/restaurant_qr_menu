<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('restaurant_table_id')
                ->constrained('restaurant_tables')
                ->cascadeOnDelete();

            $table->string('order_number')->unique();

            $table->string('status')->default('new');
            // new, preparing, ready, completed, cancelled

            $table->decimal('total_amount', 10, 2)->default(0);
            $table->text('customer_note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};