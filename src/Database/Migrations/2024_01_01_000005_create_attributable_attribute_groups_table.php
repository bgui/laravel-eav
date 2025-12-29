<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('attributable_attribute_groups')) {
            Schema::create('attributable_attribute_groups', function (Blueprint $table) {
            $table->id();
            // Create morph columns manually to control index names
            $table->string('attributable_type');
            $table->unsignedBigInteger('attributable_id');
            $table->unsignedBigInteger('attribute_group_id');
            $table->timestamps();

            // Index for morph columns with short name
            $table->index(['attributable_type', 'attributable_id'], 'idx_aag_attributable');
            
            $table->unique(['attributable_type', 'attributable_id', 'attribute_group_id'], 'aag_attr_group_unique');
            });
            
            // Add foreign key only if parent table exists
            if (Schema::hasTable('attribute_groups')) {
                Schema::table('attributable_attribute_groups', function (Blueprint $table) {
                    $table->foreign('attribute_group_id')
                        ->references('id')
                        ->on('attribute_groups')
                        ->onDelete('cascade');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attributable_attribute_groups');
    }
};


