<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convert Laratrust `roles` / `permissions` tables to Spatie's schema.
     */
    public function up(): void
    {
        $this->convertTable('roles');
        $this->convertTable('permissions');

        $this->changeToUnsignedBigIntegerIfNeeded('model_has_roles', 'role_id');
        $this->changeToUnsignedBigIntegerIfNeeded('role_has_permissions', 'role_id');
        $this->changeToUnsignedBigIntegerIfNeeded('role_has_permissions', 'permission_id');
        $this->changeToUnsignedBigIntegerIfNeeded('model_has_permissions', 'permission_id');
        $this->changeToUnsignedBigIntegerIfNeeded('role_user', 'role_id');
        $this->changeToUnsignedBigIntegerIfNeeded('permission_role', 'role_id');
        $this->changeToUnsignedBigIntegerIfNeeded('permission_role', 'permission_id');
        $this->changeToUnsignedBigIntegerIfNeeded('permission_user', 'permission_id');

        $this->restoreForeignKeys();
    }

    public function down(): void
    {
        // Irreversible: Laratrust-only columns are dropped.
    }

    private function convertTable(string $table): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $this->dropForeignKeysReferencing($table);

        if (Schema::hasColumn($table, 'display_name') || Schema::hasColumn($table, 'description')) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                $drop = [];
                if (Schema::hasColumn($table, 'display_name')) {
                    $drop[] = 'display_name';
                }
                if (Schema::hasColumn($table, 'description')) {
                    $drop[] = 'description';
                }
                if ($drop !== []) {
                    $blueprint->dropColumn($drop);
                }
            });
        }

        if (! Schema::hasColumn($table, 'guard_name')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('guard_name')->default('web')->after('name');
            });
        }

        $legacyUnique = $table.'_name_unique';
        if (Schema::hasIndex($table, $legacyUnique)) {
            Schema::table($table, function (Blueprint $blueprint) use ($legacyUnique) {
                $blueprint->dropUnique($legacyUnique);
            });
        }

        if (! Schema::hasIndex($table, ['name', 'guard_name'])) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->unique(['name', 'guard_name']);
            });
        }

        $this->changeToUnsignedBigIntegerIfNeeded($table, 'id', true);
    }

    private function changeToUnsignedBigIntegerIfNeeded(string $table, string $column, bool $autoIncrement = false): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $typeName = collect(Schema::getColumns($table))->firstWhere('name', $column)['type_name'] ?? null;
        if (in_array($typeName, ['bigint', 'int8'], true)) {
            return;
        }

        $this->dropForeignKeysOn($table);

        $sql = $autoIncrement
            ? "ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT"
            : "ALTER TABLE `{$table}` MODIFY `{$column}` BIGINT UNSIGNED NOT NULL";

        DB::statement($sql);
    }

    private function dropForeignKeysReferencing(string $referencedTable): void
    {
        foreach ($this->relatedTables() as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach (Schema::getForeignKeys($table) as $foreignKey) {
                if (($foreignKey['foreign_table'] ?? null) === $referencedTable) {
                    Schema::table($table, function (Blueprint $blueprint) use ($foreignKey) {
                        $blueprint->dropForeign($foreignKey['name']);
                    });
                }
            }
        }
    }

    private function dropForeignKeysOn(string $table): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            Schema::table($table, function (Blueprint $blueprint) use ($foreignKey) {
                $blueprint->dropForeign($foreignKey['name']);
            });
        }
    }

    private function restoreForeignKeys(): void
    {
        $this->addForeignIfMissing('model_has_roles', 'model_has_roles_role_id_foreign', 'role_id', 'roles');
        $this->addForeignIfMissing('model_has_permissions', 'model_has_permissions_permission_id_foreign', 'permission_id', 'permissions');
        $this->addForeignIfMissing('role_has_permissions', 'role_has_permissions_role_id_foreign', 'role_id', 'roles');
        $this->addForeignIfMissing('role_has_permissions', 'role_has_permissions_permission_id_foreign', 'permission_id', 'permissions');
        $this->addForeignIfMissing('role_user', 'role_user_role_id_foreign', 'role_id', 'roles');
        $this->addForeignIfMissing('permission_role', 'permission_role_role_id_foreign', 'role_id', 'roles');
        $this->addForeignIfMissing('permission_role', 'permission_role_permission_id_foreign', 'permission_id', 'permissions');
        $this->addForeignIfMissing('permission_user', 'permission_user_permission_id_foreign', 'permission_id', 'permissions');
    }

    private function addForeignIfMissing(string $table, string $constraint, string $column, string $parentTable): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasTable($parentTable)) {
            return;
        }

        $exists = collect(Schema::getForeignKeys($table))->contains(fn (array $fk) => ($fk['name'] ?? null) === $constraint);
        if ($exists) {
            return;
        }

        $this->deleteOrphans($table, $column, $parentTable);

        Schema::table($table, function (Blueprint $blueprint) use ($constraint, $column, $parentTable) {
            $blueprint->foreign($column, $constraint)
                ->references('id')
                ->on($parentTable)
                ->onDelete('cascade');
        });
    }

    private function deleteOrphans(string $table, string $column, string $parentTable): void
    {
        DB::table($table)
            ->whereNotIn($column, DB::table($parentTable)->select('id'))
            ->delete();
    }

    /**
     * @return array<int, string>
     */
    private function relatedTables(): array
    {
        return [
            'model_has_roles',
            'model_has_permissions',
            'role_has_permissions',
            'role_user',
            'permission_role',
            'permission_user',
        ];
    }
};
