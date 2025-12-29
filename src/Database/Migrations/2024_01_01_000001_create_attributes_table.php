<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('attributes')) {
            Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->uuid('logical_id')->unique();
            $table->string('title')->index();
            $table->string('slug')->unique()->index();
            $table->smallInteger('type');
            $table->text('description')->nullable();
            $table->json('validations')->nullable();
            $table->json('values')->nullable();
            $table->boolean('is_active')->default(true);
            $table->char('language', 2)->default('en');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
