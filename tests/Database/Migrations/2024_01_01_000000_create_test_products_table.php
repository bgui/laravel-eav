<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('test_products')) {
            Schema::create('test_products', function (Blueprint $table) {
                $table->id();
                // No timestamps needed for test model
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('test_products');
    }
};

