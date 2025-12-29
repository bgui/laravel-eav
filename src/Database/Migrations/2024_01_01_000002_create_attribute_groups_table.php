<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('attribute_groups')) {
            Schema::create('attribute_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('module_id')->nullable();
            $table->string('title')->index();
            $table->string('slug')->unique()->index();
            $table->boolean('is_active')->default(true);
            $table->char('language', 2)->default('en');

            if (Schema::hasTable('modules')) {
                $table->foreign('module_id')->references('id')->on('modules')->cascadeOnDelete()->cascadeOnUpdate();
            }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_groups');
    }
};


