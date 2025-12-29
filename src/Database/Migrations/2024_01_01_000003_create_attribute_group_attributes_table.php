<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('attribute_group_attributes')) {
            Schema::create('attribute_group_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attribute_group_id');
            $table->unsignedBigInteger('attribute_id');

            $table->unique(['attribute_group_id', 'attribute_id'], 'aga_group_attr_unique');
            });
            
            // Add foreign keys only if parent tables exist
            if (Schema::hasTable('attribute_groups')) {
                Schema::table('attribute_group_attributes', function (Blueprint $table) {
                    $table->foreign('attribute_group_id')
                        ->references('id')
                        ->on('attribute_groups')
                        ->onDelete('cascade');
                });
            }
            
            if (Schema::hasTable('attributes')) {
                Schema::table('attribute_group_attributes', function (Blueprint $table) {
                    $table->foreign('attribute_id')
                        ->references('id')
                        ->on('attributes')
                        ->onDelete('cascade');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_group_attributes');
    }
};


