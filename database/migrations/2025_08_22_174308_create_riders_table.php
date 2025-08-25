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
        Schema::create('riders', function (Blueprint $table) {
            $table->id();
            $table->string('profile_photo')->nullable();
            $table->string('rider_id')->unique();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->unique();
            $table->string('image')->nullable();
            $table->boolean('online')->default(false);
            $table->integer('delivered_orders')->default(0);
            $table->float('average_rating', 3, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riders');
    }
};
