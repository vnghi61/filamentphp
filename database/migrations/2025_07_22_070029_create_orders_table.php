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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number');
            $table->double('shipping_fee');
            $table->double('discount');
            $table->double('subtotal')->default(0);
            $table->double('total')->default(0);
            $table->double('paid_amount')->default(0);
            $table->double('due_amount')->default(0);
            $table->string('order_type');
            $table->string('order_status');
            $table->string('payment_status');
            $table->text('note')->nullable();
            $table->double('total_quantity')->default(0);
            $table->double('total_items')->default(0);
            $table->string('payment_method');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
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
