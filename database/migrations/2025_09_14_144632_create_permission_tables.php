<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Laratrust already created `roles` and `permissions`, so this Spatie
     * migration only adds missing columns/indexes and the Spatie pivot tables.
     */
    public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        throw_if(empty($tableNames), Exception::class, 'Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        throw_if($teams && empty($columnNames['team_foreign_key'] ?? null), Exception::class, 'Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');

        if (! Schema::hasTable($tableNames['permissions'])) {
            Schema::create($tableNames['permissions'], static function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();

                $table->unique(['name', 'guard_name']);
            });
        } else {
            $this->ensureGuardNameColumn($tableNames['permissions']);
            $this->ensureNameGuardUnique($tableNames['permissions']);
        }

        if (! Schema::hasTable($tableNames['roles'])) {
            Schema::create($tableNames['roles'], static function (Blueprint $table) use ($teams, $columnNames) {
                $table->bigIncrements('id');
                if ($teams || config('permission.testing')) {
                    $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                    $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
                }
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                if ($teams || config('permission.testing')) {
                    $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
                } else {
                    $table->unique(['name', 'guard_name']);
                }
            });
        } else {
            $this->ensureGuardNameColumn($tableNames['roles']);
            $this->ensureNameGuardUnique($tableNames['roles'], $teams, $columnNames['team_foreign_key'] ?? 'team_id');
        }

        if (! Schema::hasTable($tableNames['model_has_permissions'])) {
            Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
                $this->addUnsignedForeignId($table, $pivotPermission, $tableNames['permissions']);

                $table->string('model_type');
                $table->unsignedBigInteger($columnNames['model_morph_key']);
                $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

                $table->foreign($pivotPermission)
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');
                if ($teams) {
                    $table->unsignedBigInteger($columnNames['team_foreign_key']);
                    $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');

                    $table->primary([$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                        'model_has_permissions_permission_model_type_primary');
                } else {
                    $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'],
                        'model_has_permissions_permission_model_type_primary');
                }
            });
        }

        if (! Schema::hasTable($tableNames['model_has_roles'])) {
            Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
                $this->addUnsignedForeignId($table, $pivotRole, $tableNames['roles']);

                $table->string('model_type');
                $table->unsignedBigInteger($columnNames['model_morph_key']);
                $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

                $table->foreign($pivotRole)
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');
                if ($teams) {
                    $table->unsignedBigInteger($columnNames['team_foreign_key']);
                    $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

                    $table->primary([$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'],
                        'model_has_roles_role_model_type_primary');
                } else {
                    $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'],
                        'model_has_roles_role_model_type_primary');
                }
            });
        }

        if (! Schema::hasTable($tableNames['role_has_permissions'])) {
            Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
                $this->addUnsignedForeignId($table, $pivotPermission, $tableNames['permissions']);
                $this->addUnsignedForeignId($table, $pivotRole, $tableNames['roles']);

                $table->foreign($pivotPermission)
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');

                $table->foreign($pivotRole)
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');

                $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
            });
        }

        $this->copyLaratrustAssignments($tableNames, $pivotRole, $pivotPermission);

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        throw_if(empty($tableNames), Exception::class, 'Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');

        Schema::dropIfExists($tableNames['role_has_permissions']);
        Schema::dropIfExists($tableNames['model_has_roles']);
        Schema::dropIfExists($tableNames['model_has_permissions']);
    }

    private function ensureGuardNameColumn(string $table): void
    {
        if (Schema::hasColumn($table, 'guard_name')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->string('guard_name')->default('web')->after('name');
        });
    }

    private function ensureNameGuardUnique(string $table, bool $teams = false, string $teamForeignKey = 'team_id'): void
    {
        $legacyUnique = $table.'_name_unique';
        if (Schema::hasIndex($table, $legacyUnique)) {
            Schema::table($table, function (Blueprint $blueprint) use ($legacyUnique) {
                $blueprint->dropUnique($legacyUnique);
            });
        }

        $uniqueColumns = ($teams || config('permission.testing'))
            ? [$teamForeignKey, 'name', 'guard_name']
            : ['name', 'guard_name'];

        if (! Schema::hasIndex($table, $uniqueColumns)) {
            Schema::table($table, function (Blueprint $blueprint) use ($uniqueColumns) {
                $blueprint->unique($uniqueColumns);
            });
        }
    }

    private function addUnsignedForeignId(Blueprint $table, string $column, string $parentTable): void
    {
        $typeName = collect(Schema::getColumns($parentTable))->firstWhere('name', 'id')['type_name'] ?? 'bigint';

        if (in_array($typeName, ['bigint', 'int8'], true)) {
            $table->unsignedBigInteger($column);
        } else {
            $table->unsignedInteger($column);
        }
    }

    private function copyLaratrustAssignments(array $tableNames, string $pivotRole, string $pivotPermission): void
    {
        $modelType = \App\Models\User::class;

        if (Schema::hasTable('role_user') && Schema::hasTable($tableNames['model_has_roles'])) {
            $rows = DB::table('role_user')->get(['role_id', 'user_id'])->map(function ($row) use ($pivotRole, $modelType) {
                return [
                    $pivotRole => $row->role_id,
                    'model_type' => $modelType,
                    'model_id' => $row->user_id,
                ];
            })->all();

            foreach (array_chunk($rows, 100) as $chunk) {
                DB::table($tableNames['model_has_roles'])->insertOrIgnore($chunk);
            }
        }

        if (Schema::hasTable('permission_user') && Schema::hasTable($tableNames['model_has_permissions'])) {
            $rows = DB::table('permission_user')->get(['permission_id', 'user_id'])->map(function ($row) use ($pivotPermission, $modelType) {
                return [
                    $pivotPermission => $row->permission_id,
                    'model_type' => $modelType,
                    'model_id' => $row->user_id,
                ];
            })->all();

            foreach (array_chunk($rows, 100) as $chunk) {
                DB::table($tableNames['model_has_permissions'])->insertOrIgnore($chunk);
            }
        }

        if (Schema::hasTable('permission_role') && Schema::hasTable($tableNames['role_has_permissions'])) {
            DB::statement(
                'INSERT IGNORE INTO `'.$tableNames['role_has_permissions'].'` (`'.$pivotPermission.'`, `'.$pivotRole.'`)
                 SELECT `permission_id`, `role_id` FROM `permission_role`'
            );
        }
    }
};
