<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        // ── role_has_permissions ──
        Schema::table('role_has_permissions', function (Blueprint $table) {
            if (Schema::hasColumn('role_has_permissions', 'divisions')) {
                $table->dropColumn('divisions');
            }
            if (! Schema::hasColumn('role_has_permissions', 'division_id')) {
                $table->unsignedBigInteger('division_id')->nullable()->after('role_id');
            }
        });

        $this->replacePrimaryKey('role_has_permissions', ['permission_id', 'role_id', 'division_id']);

        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->foreign('division_id')->references('id')->on('user_divisions')->nullOnDelete();
        });

        // ── model_has_permissions ──
        Schema::table('model_has_permissions', function (Blueprint $table) {
            if (Schema::hasColumn('model_has_permissions', 'divisions')) {
                $table->dropColumn('divisions');
            }
            if (! Schema::hasColumn('model_has_permissions', 'division_id')) {
                $table->unsignedBigInteger('division_id')->nullable()->after('model_id');
            }
        });

        $this->replacePrimaryKey('model_has_permissions', ['permission_id', 'model_id', 'model_type', 'division_id'], 'mhp_pid_mid_mt_did_unique');

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->foreign('division_id')->references('id')->on('user_divisions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->dropIndex(['permission_id', 'role_id', 'division_id']);
            $table->dropForeign(['division_id']);
            $table->dropColumn('division_id');
            $table->primary(['permission_id', 'role_id']);
            $table->json('divisions')->nullable()->after('role_id');
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropIndex('mhp_pid_mid_mt_did_unique');
            $table->dropForeign(['division_id']);
            $table->dropColumn('division_id');
            $table->primary(['permission_id', 'model_id', 'model_type']);
            $table->json('divisions')->nullable()->after('model_id');
        });
    }

    /**
     * Replace primary key with unique index.
     * MySQL: must drop FK constraints before DROP PRIMARY KEY (errno 150).
     */
    protected function replacePrimaryKey(string $table, array $columns, ?string $indexName = null): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::table($table, function (Blueprint $tbl) use ($columns, $indexName) {
                $tbl->dropPrimary('primary');
                $indexName ? $tbl->unique($columns, $indexName) : $tbl->unique($columns);
            });
        } else {
            // MySQL/MariaDB: drop ALL FK constraints, then drop PK, then re-add FKs
            $fks = $this->getForeignKeys($table);

            foreach (array_keys($fks) as $fkName) {
                DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkName}`");
            }

            DB::statement("ALTER TABLE `{$table}` DROP PRIMARY KEY");

            Schema::table($table, function (Blueprint $tbl) use ($columns, $indexName) {
                $indexName ? $tbl->unique($columns, $indexName) : $tbl->unique($columns);
            });

            // Re-add FK constraints (except division_id which is added separately)
            foreach ($fks as $fkName => $fkColumns) {
                $refTable = $fkColumns['ref_table'];
                $refColumn = $fkColumns['ref_column'];
                $onDelete = $fkColumns['on_delete'] ?? 'CASCADE';
                DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$fkName}` FOREIGN KEY (`{$fkColumns['column']}`) REFERENCES `{$refTable}` (`{$refColumn}`) ON DELETE {$onDelete}");
            }
        }
    }

    /**
     * Get foreign key constraints for a table.
     *
     * @return array<string, array{column: string, ref_table: string, ref_column: string, on_delete: string}>
     */
    protected function getForeignKeys(string $table): array
    {
        $database = DB::getDatabaseName();

        $rows = DB::select(
            "SELECT kcu.CONSTRAINT_NAME, kcu.COLUMN_NAME, kcu.REFERENCED_TABLE_NAME, kcu.REFERENCED_COLUMN_NAME, rc.DELETE_RULE
             FROM information_schema.KEY_COLUMN_USAGE kcu
             JOIN information_schema.REFERENTIAL_CONSTRAINTS rc ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME AND kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA
             WHERE kcu.TABLE_SCHEMA = ? AND kcu.TABLE_NAME = ? AND kcu.REFERENCED_TABLE_NAME IS NOT NULL",
            [$database, $table]
        );

        $fks = [];
        foreach ($rows as $row) {
            // Skip the division_id FK — it's added separately after PK replacement
            if ($row->COLUMN_NAME === 'division_id') {
                continue;
            }
            $fks[$row->CONSTRAINT_NAME] = [
                'column' => $row->COLUMN_NAME,
                'ref_table' => $row->REFERENCED_TABLE_NAME,
                'ref_column' => $row->REFERENCED_COLUMN_NAME,
                'on_delete' => $row->DELETE_RULE,
            ];
        }

        return $fks;
    }
};
