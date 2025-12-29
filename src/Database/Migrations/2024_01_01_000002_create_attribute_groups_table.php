<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id')->nullable();
            $table->string('title')->index();
            $table->string('slug')->unique()->index();
            $table->boolean('is_active')->default(true);
            $table->char('language', 2)->default('en');

            // Foreign key to modules table (optional - only if modules table exists)
            // Projects can add this foreign key manually if they have a modules table
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_groups');
    }
};
