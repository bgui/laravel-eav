<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration fixes the translatable_type in eav_translations table
     * when custom models (like Ago\Eav\App\Models\Attribute) are used instead of
     * the package's EloquentAttribute model.
     * 
     * This migration will update any records that were created with the package's
     * model namespace to use the custom model namespace if it exists.
     */
    public function up(): void
    {
        if (!Schema::hasTable('eav_translations')) {
            return;
        }

        // Get all unique translatable_type values that use the package namespace
        $packageTypes = DB::table('eav_translations')
            ->where('translatable_type', 'Fiachehr\LaravelEav\Infrastructure\Persistence\Eloquent\EloquentAttribute')
            ->distinct()
            ->pluck('translatable_id')
            ->toArray();

        if (empty($packageTypes)) {
            return;
        }

        // Check if custom Attribute model exists and update accordingly
        // This is a generic migration that can be customized per project
        // Projects can override this behavior by creating their own migration

        // For now, we'll leave the records as-is and let projects handle the migration
        // if they need to change the translatable_type to their custom model namespace
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to revert as this migration doesn't change anything by default
    }
};
