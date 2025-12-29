<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attributable_attributes', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('attributable_attributes', 'value_text')) {
                $table->text('value_text')->nullable()->after('attribute_id');
            }
            if (!Schema::hasColumn('attributable_attributes', 'value_number')) {
                $table->bigInteger('value_number')->nullable()->after('value_text');
            }
            if (!Schema::hasColumn('attributable_attributes', 'value_decimal')) {
                $table->decimal('value_decimal', 15, 4)->nullable()->after('value_number');
            }
            if (!Schema::hasColumn('attributable_attributes', 'value_date')) {
                $table->date('value_date')->nullable()->after('value_decimal');
            }
            if (!Schema::hasColumn('attributable_attributes', 'value_datetime')) {
                $table->dateTime('value_datetime')->nullable()->after('value_date');
            }
            if (!Schema::hasColumn('attributable_attributes', 'value_time')) {
                $table->time('value_time')->nullable()->after('value_datetime');
            }
            if (!Schema::hasColumn('attributable_attributes', 'value_boolean')) {
                $table->boolean('value_boolean')->nullable()->after('value_time');
            }
            if (!Schema::hasColumn('attributable_attributes', 'value_json')) {
                $table->json('value_json')->nullable()->after('value_boolean');
            }
            if (!Schema::hasColumn('attributable_attributes', 'created_at')) {
                $table->timestamps();
            }
        });

        // Add all indexes outside Schema::table using helper method
        $this->addIndexIfNotExists('idx_value_number', function() {
            Schema::table('attributable_attributes', function (Blueprint $table) {
                $table->index('value_number', 'idx_value_number');
            });
        });
        
        $this->addIndexIfNotExists('idx_value_decimal', function() {
            Schema::table('attributable_attributes', function (Blueprint $table) {
                $table->index('value_decimal', 'idx_value_decimal');
            });
        });
        
        $this->addIndexIfNotExists('idx_value_date', function() {
            Schema::table('attributable_attributes', function (Blueprint $table) {
                $table->index('value_date', 'idx_value_date');
            });
        });
        
        $this->addIndexIfNotExists('idx_value_datetime', function() {
            Schema::table('attributable_attributes', function (Blueprint $table) {
                $table->index('value_datetime', 'idx_value_datetime');
            });
        });
        
        $this->addIndexIfNotExists('idx_value_boolean', function() {
            Schema::table('attributable_attributes', function (Blueprint $table) {
                $table->index('value_boolean', 'idx_value_boolean');
            });
        });
        
        $this->addIndexIfNotExists('idx_attr_number', function() {
            Schema::table('attributable_attributes', function (Blueprint $table) {
                $table->index(['attribute_id', 'value_number'], 'idx_attr_number');
            });
        });
        
        $this->addIndexIfNotExists('idx_attr_date', function() {
            Schema::table('attributable_attributes', function (Blueprint $table) {
                $table->index(['attribute_id', 'value_date'], 'idx_attr_date');
            });
        });
        
        $this->addIndexIfNotExists('idx_attr_boolean', function() {
            Schema::table('attributable_attributes', function (Blueprint $table) {
                $table->index(['attribute_id', 'value_boolean'], 'idx_attr_boolean');
            });
        });
        
        // Add prefix indexes for TEXT columns using DB::statement
        // Note: SQLite doesn't support prefix indexes, so we skip them for SQLite
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'sqlite') {
            $this->addIndexIfNotExists('idx_value_text', function() {
                DB::statement('CREATE INDEX idx_value_text ON attributable_attributes (value_text(255))');
            });
            
            $this->addIndexIfNotExists('idx_attr_text', function() {
                DB::statement('CREATE INDEX idx_attr_text ON attributable_attributes (attribute_id, value_text(255))');
            });
        } else {
            // For SQLite, create regular indexes without prefix
            $this->addIndexIfNotExists('idx_value_text', function() {
                Schema::table('attributable_attributes', function (Blueprint $table) {
                    $table->index('value_text', 'idx_value_text');
                });
            });
            
            $this->addIndexIfNotExists('idx_attr_text', function() {
                Schema::table('attributable_attributes', function (Blueprint $table) {
                    $table->index(['attribute_id', 'value_text'], 'idx_attr_text');
                });
            });
        }
    }

    /**
     * Check if index exists and add it if not
     */
    protected function addIndexIfNotExists(string $indexName, callable $callback): void
    {
        // Use Laravel's Schema::hasIndex for cross-database compatibility
        if (!Schema::hasIndex('attributable_attributes', $indexName)) {
            try {
                $callback();
            } catch (\Exception $e) {
                // Index might have been created by another process, ignore error
                if (strpos($e->getMessage(), 'Duplicate key name') === false && 
                    strpos($e->getMessage(), 'already exists') === false &&
                    strpos($e->getMessage(), 'duplicate') === false) {
                    throw $e;
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('attributable_attributes', function (Blueprint $table) {
            // Drop indexes if they exist (using try-catch to handle if they don't exist)
            $indexes = [
                'idx_attr_boolean',
                'idx_attr_date',
                'idx_attr_number',
                'idx_attr_text',
                'idx_value_boolean',
                'idx_value_datetime',
                'idx_value_date',
                'idx_value_decimal',
                'idx_value_number',
                'idx_value_text',
            ];

            foreach ($indexes as $indexName) {
                try {
                    $table->dropIndex($indexName);
                } catch (\Exception $e) {
                    // Index doesn't exist, ignore error
                }
            }

            // Drop columns if they exist
            $columns = [
                'value_text',
                'value_number',
                'value_decimal',
                'value_date',
                'value_datetime',
                'value_time',
                'value_boolean',
                'value_json',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('attributable_attributes', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Drop timestamps if they exist
            if (Schema::hasColumn('attributable_attributes', 'created_at')) {
                $table->dropColumn(['created_at', 'updated_at']);
            }
        });
    }
};

