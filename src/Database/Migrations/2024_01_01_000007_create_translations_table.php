<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration creates an eav_translations table for multilingual support.
     * The table name is prefixed with 'eav_' to avoid conflicts with user's project tables.
     */
    public function up(): void
    {
        if (!Schema::hasTable('eav_translations')) {
            Schema::create('eav_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('translatable_id');
                $table->string('translatable_type');
                $table->string('locale', 10)->index();
                $table->string('key')->index();
                $table->text('value')->nullable();
                
                // Index for efficient lookups
                $table->index(['translatable_type', 'translatable_id', 'locale', 'key'], 'eav_translatable_lookup');
                
                // Unique constraint to prevent duplicate translations
                $table->unique(['translatable_type', 'translatable_id', 'locale', 'key'], 'eav_translation_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eav_translations');
    }
};

