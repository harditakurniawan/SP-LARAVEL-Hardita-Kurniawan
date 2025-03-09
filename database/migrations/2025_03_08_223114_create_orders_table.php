<?php

use App\Enums\OrderStatusEnum;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('trx_id', 100)->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->dateTime('order_date')->useCurrent();
            $table->dateTime('expired_date')->useCurrent();
            $table->integer('quantity')->default(1);
            $table->decimal('total_price', 10, 2);
            $table->string('product_name', 150);
            $table->decimal('product_price', 10, 2);
            $table->decimal('product_discount', 10, 2);
            $table->enum('status', array_map(fn($status) => $status->value, OrderStatusEnum::cases()))->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
