<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SchemaSeeder extends Seeder
{
    /**
     * Seed a realistic set of schema-builder projects for every user.
     */
    public function run(): void
    {
        User::query()->each(function (User $user): void {
            if ($user->schemaProjects()->exists()) {
                return;
            }

            $this->createProject($user, 'Blog Platform', $this->blogTables());

            foreach ($this->extraProjects() as $name => $tables) {
                $this->createProject($user, $name, $tables);
            }
        });
    }

    /**
     * @param  list<array<string, mixed>>  $tables
     */
    private function createProject(User $user, string $name, array $tables): void
    {
        $project = $user->schemaProjects()->create(['name' => $name]);

        foreach ($tables as $sort => $table) {
            $row = $project->tables()->create([
                'client_id' => $table['id'],
                'name' => $table['name'],
                'color' => $table['color'],
                'pos_x' => $table['x'],
                'pos_y' => $table['y'],
                'indexes' => $table['indexes'] ?? [],
                'sort' => $sort,
            ]);

            foreach ($table['columns'] as $colSort => $col) {
                $row->columns()->create([
                    'client_id' => $col['id'],
                    'name' => $col['name'],
                    'type' => $col['type'],
                    'is_nullable' => $col['nullable'] ?? false,
                    'is_pk' => $col['pk'] ?? false,
                    'is_unique' => $col['unique'] ?? false,
                    'is_index' => $col['index'] ?? false,
                    'default_value' => ($col['default'] ?? '') !== '' ? $col['default'] : null,
                    'fk_table' => $col['fk']['table'] ?? null,
                    'fk_column' => $col['fk']['column'] ?? null,
                    'sort' => $colSort,
                ]);
            }
        }
    }

    /**
     * Column helper matching the design's seed shape.
     *
     * @param  array<string, mixed>  $opts
     * @return array<string, mixed>
     */
    private function col(string $id, string $name, string $type, array $opts = []): array
    {
        return array_merge([
            'id' => $id, 'name' => $name, 'type' => $type,
            'nullable' => false, 'pk' => false, 'unique' => false, 'index' => false,
            'default' => '', 'fk' => null,
        ], $opts);
    }

    /**
     * The seeded "Blog Platform" schema — a typical Laravel blog/auth app.
     *
     * @return list<array<string, mixed>>
     */
    private function blogTables(): array
    {
        return [
            [
                'id' => 't_users', 'name' => 'users', 'color' => 'blue', 'x' => 80, 'y' => 96,
                'indexes' => ['users_email_unique'],
                'columns' => [
                    $this->col('u1', 'id', 'id', ['pk' => true]),
                    $this->col('u2', 'name', 'string'),
                    $this->col('u3', 'email', 'string', ['unique' => true]),
                    $this->col('u4', 'email_verified_at', 'datetime', ['nullable' => true]),
                    $this->col('u5', 'password', 'string'),
                    $this->col('u6', 'remember_token', 'string', ['nullable' => true]),
                    $this->col('u7', 'created_at', 'timestamp', ['nullable' => true]),
                    $this->col('u8', 'updated_at', 'timestamp', ['nullable' => true]),
                ],
            ],
            [
                'id' => 't_posts', 'name' => 'posts', 'color' => 'green', 'x' => 520, 'y' => 60,
                'indexes' => ['posts_user_id_published_at_index'],
                'columns' => [
                    $this->col('p1', 'id', 'id', ['pk' => true]),
                    $this->col('p2', 'user_id', 'unsignedBigInteger', ['fk' => ['table' => 't_users', 'column' => 'id'], 'index' => true]),
                    $this->col('p3', 'title', 'string'),
                    $this->col('p4', 'slug', 'string', ['unique' => true]),
                    $this->col('p5', 'body', 'text'),
                    $this->col('p6', 'published_at', 'datetime', ['nullable' => true]),
                    $this->col('p7', 'created_at', 'timestamp', ['nullable' => true]),
                    $this->col('p8', 'updated_at', 'timestamp', ['nullable' => true]),
                ],
            ],
            [
                'id' => 't_comments', 'name' => 'comments', 'color' => 'purple', 'x' => 968, 'y' => 130,
                'indexes' => [],
                'columns' => [
                    $this->col('cm1', 'id', 'id', ['pk' => true]),
                    $this->col('cm2', 'post_id', 'unsignedBigInteger', ['fk' => ['table' => 't_posts', 'column' => 'id'], 'index' => true]),
                    $this->col('cm3', 'user_id', 'unsignedBigInteger', ['fk' => ['table' => 't_users', 'column' => 'id'], 'index' => true]),
                    $this->col('cm4', 'body', 'text'),
                    $this->col('cm5', 'approved', 'boolean', ['default' => 'false']),
                    $this->col('cm6', 'created_at', 'timestamp', ['nullable' => true]),
                    $this->col('cm7', 'updated_at', 'timestamp', ['nullable' => true]),
                ],
            ],
            [
                'id' => 't_roles', 'name' => 'roles', 'color' => 'amber', 'x' => 80, 'y' => 540,
                'indexes' => [],
                'columns' => [
                    $this->col('r1', 'id', 'id', ['pk' => true]),
                    $this->col('r2', 'name', 'string', ['unique' => true]),
                    $this->col('r3', 'label', 'string', ['nullable' => true]),
                    $this->col('r4', 'created_at', 'timestamp', ['nullable' => true]),
                    $this->col('r5', 'updated_at', 'timestamp', ['nullable' => true]),
                ],
            ],
            [
                'id' => 't_role_user', 'name' => 'role_user', 'color' => 'red', 'x' => 480, 'y' => 560,
                'indexes' => ['role_user_role_id_user_id_unique'],
                'columns' => [
                    $this->col('ru1', 'id', 'id', ['pk' => true]),
                    $this->col('ru2', 'role_id', 'unsignedBigInteger', ['fk' => ['table' => 't_roles', 'column' => 'id']]),
                    $this->col('ru3', 'user_id', 'unsignedBigInteger', ['fk' => ['table' => 't_users', 'column' => 'id']]),
                ],
            ],
            [
                'id' => 't_categories', 'name' => 'categories', 'color' => 'teal', 'x' => 968, 'y' => 540,
                'indexes' => [],
                'columns' => [
                    $this->col('ca1', 'id', 'id', ['pk' => true]),
                    $this->col('ca2', 'name', 'string'),
                    $this->col('ca3', 'slug', 'string', ['unique' => true]),
                    $this->col('ca4', 'created_at', 'timestamp', ['nullable' => true]),
                    $this->col('ca5', 'updated_at', 'timestamp', ['nullable' => true]),
                ],
            ],
            [
                'id' => 't_category_post', 'name' => 'category_post', 'color' => 'orange', 'x' => 600, 'y' => 860,
                'indexes' => ['category_post_post_id_category_id_unique'],
                'columns' => [
                    $this->col('cp1', 'id', 'id', ['pk' => true]),
                    $this->col('cp2', 'post_id', 'unsignedBigInteger', ['fk' => ['table' => 't_posts', 'column' => 'id']]),
                    $this->col('cp3', 'category_id', 'unsignedBigInteger', ['fk' => ['table' => 't_categories', 'column' => 'id']]),
                ],
            ],
        ];
    }

    /**
     * A few extra projects so the dashboard grid feels populated.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function extraProjects(): array
    {
        return [
            'E-commerce API' => $this->simpleTables(['products' => 'teal', 'orders' => 'orange', 'order_items' => 'red', 'customers' => 'blue']),
            'SaaS Billing' => $this->simpleTables(['subscriptions' => 'purple', 'invoices' => 'pink', 'plans' => 'blue', 'payments' => 'green']),
            'Inventory System' => $this->simpleTables(['items' => 'amber', 'warehouses' => 'teal', 'stock_levels' => 'red', 'suppliers' => 'orange']),
            'CRM Core' => $this->simpleTables(['leads' => 'green', 'contacts' => 'blue', 'deals' => 'purple', 'activities' => 'teal']),
        ];
    }

    /**
     * Build a small grid of plain tables (id + name + timestamps) for filler projects.
     *
     * @param  array<string, string>  $names  table name => color
     * @return list<array<string, mixed>>
     */
    private function simpleTables(array $names): array
    {
        $tables = [];
        $i = 0;
        foreach ($names as $name => $color) {
            $cid = 't_'.$name;
            $tables[] = [
                'id' => $cid, 'name' => $name, 'color' => $color,
                'x' => 80 + ($i % 2) * 360, 'y' => 80 + intdiv($i, 2) * 300,
                'indexes' => [],
                'columns' => [
                    $this->col($cid.'_1', 'id', 'id', ['pk' => true]),
                    $this->col($cid.'_2', 'name', 'string'),
                    $this->col($cid.'_3', 'created_at', 'timestamp', ['nullable' => true]),
                    $this->col($cid.'_4', 'updated_at', 'timestamp', ['nullable' => true]),
                ],
            ];
            $i++;
        }

        return $tables;
    }
}
