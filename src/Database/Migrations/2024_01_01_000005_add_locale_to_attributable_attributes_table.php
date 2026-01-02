<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attributable_attributes', function (Blueprint $table) {
            $table->string('locale', 10)->nullable()->after('attribute_id');
            
            // Update unique constraint to include locale
            $table->dropUnique('unique_attributable_attribute');
            $table->unique(['attributable_type', 'attributable_id', 'attribute_id', 'locale'], 'unique_attributable_attribute_locale');
        });
    }

    public function down(): void
    {
        Schema::table('attributable_attributes', function (Blueprint $table) {
            // Restore original unique constraint
            $table->dropUnique('unique_attributable_attribute_locale');
            $table->unique(['attributable_type', 'attributable_id', 'attribute_id'], 'unique_attributable_attribute');
            
            $table->dropColumn('locale');
        });
    }
};

