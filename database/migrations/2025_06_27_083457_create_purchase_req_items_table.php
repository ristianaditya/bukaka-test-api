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
        Schema::create('purchase_req_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_req_id');
            $table->string('item_name');
            $table->string('description')->nullable();
            $table->integer('quantity');
            $table->decimal('estimated_price', 15, 2);
            $table->timestamps();

            $table->foreign('purchase_req_id')->references('id')->on('purchase_reqs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_req_items');
    }
};
