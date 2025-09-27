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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama menu, cth: Dashboard
            $table->string('route')->nullable(); // Route/URL, cth: /dashboard
            $table->string('icon')->nullable(); // Class icon, cth: uil-home-alt
            $table->unsignedBigInteger('parent_id')->default(0); // Untuk submenu, 0 = menu utama
            $table->integer('order')->default(0); // Urutan menu
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
