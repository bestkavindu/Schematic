<?php

use App\Services\Schema\PostgresSchemaCompiler;

/** A two-table schema exercising types, PK/unique/default, an FK and an index. */
function compilerSampleSchema(): array
{
    return [
        'name' => 'Blog',
        'tables' => [
            [
                'id' => 't_users', 'name' => 'users',
                'columns' => [
                    ['name' => 'id', 'type' => 'id', 'pk' => true],
                    ['name' => 'email', 'type' => 'string', 'unique' => true],
                    ['name' => 'meta', 'type' => 'json', 'nullable' => true],
                    ['name' => 'uid', 'type' => 'uuid'],
                ],
            ],
            [
                'id' => 't_posts', 'name' => 'posts',
                'columns' => [
                    ['name' => 'id', 'type' => 'id', 'pk' => true],
                    ['name' => 'author_id', 'type' => 'unsignedBigInteger', 'index' => true, 'fk' => [
                        'table' => 't_users', 'column' => 'id', 'type' => '1:N',
                        'onDelete' => 'cascade', 'onUpdate' => 'no action',
                    ]],
                    ['name' => 'status', 'type' => 'string', 'default' => 'draft'],
                ],
            ],
        ],
    ];
}

/** @param list<string> $statements */
function statementKinds(array $statements): array
{
    return array_map(function (string $s): string {
        if (str_starts_with($s, 'CREATE TABLE')) {
            return 'table';
        }
        if (str_contains($s, 'ADD CONSTRAINT')) {
            return 'fk';
        }
        if (str_starts_with($s, 'CREATE INDEX')) {
            return 'index';
        }

        return 'other';
    }, $statements);
}

test('statements are ordered: all tables, then foreign keys, then indexes', function () {
    $kinds = statementKinds((new PostgresSchemaCompiler)->compile(compilerSampleSchema()));

    $lastTable = max(array_keys($kinds, 'table', true));
    $firstFk = min(array_keys($kinds, 'fk', true));
    $firstIndex = min(array_keys($kinds, 'index', true));

    expect($lastTable)->toBeLessThan($firstFk);
    expect($firstFk)->toBeLessThan($firstIndex);
});

test('column types map to their PostgreSQL equivalents', function () {
    $sql = implode("\n", (new PostgresSchemaCompiler)->compile(compilerSampleSchema()));

    expect($sql)->toContain('"id" BIGSERIAL');
    expect($sql)->toContain('"author_id" BIGINT');          // unsignedBigInteger -> BIGINT (no UNSIGNED)
    expect($sql)->toContain('"meta" JSONB');
    expect($sql)->toContain('"uid" UUID');
    expect($sql)->toContain('"email" VARCHAR(255) NOT NULL UNIQUE');
    expect($sql)->toContain('PRIMARY KEY ("id")');
    expect($sql)->toContain("DEFAULT 'draft'");
});

test('a foreign key resolves the target client_id to its real table name and is guarded', function () {
    $sql = implode("\n", (new PostgresSchemaCompiler)->compile(compilerSampleSchema()));

    expect($sql)->toContain('ALTER TABLE "posts" ADD CONSTRAINT "posts_author_id_fkey"');
    expect($sql)->toContain('FOREIGN KEY ("author_id") REFERENCES "users" ("id")');
    expect($sql)->toContain('ON DELETE CASCADE');
    expect($sql)->not->toContain('ON UPDATE');               // 'no action' is omitted
    expect($sql)->toContain('FROM pg_constraint WHERE conname =');
});

test('an indexed column produces CREATE INDEX IF NOT EXISTS', function () {
    $sql = implode("\n", (new PostgresSchemaCompiler)->compile(compilerSampleSchema()));

    expect($sql)->toContain('CREATE INDEX IF NOT EXISTS "posts_author_id_index" ON "posts" ("author_id")');
});

test('a foreign key column adopts the referenced column type to avoid a type mismatch', function () {
    $sql = implode("\n", (new PostgresSchemaCompiler)->compile([
        'tables' => [
            ['id' => 't_projects', 'name' => 'projects', 'columns' => [
                ['name' => 'id', 'type' => 'id', 'pk' => true],
            ]],
            ['id' => 't_tasks', 'name' => 'tasks', 'columns' => [
                ['name' => 'id', 'type' => 'id', 'pk' => true],
                // Designed as a string, but points at a BIGSERIAL id — must be emitted as BIGINT.
                ['name' => 'project_id', 'type' => 'string', 'fk' => [
                    'table' => 't_projects', 'column' => 'id', 'onDelete' => 'cascade',
                ]],
            ]],
        ],
    ]));

    expect($sql)->toContain('"project_id" BIGINT');           // coerced from VARCHAR to match projects.id
    expect($sql)->not->toContain('"project_id" VARCHAR');
});

test('a uuid-referencing foreign key column is emitted as UUID', function () {
    $sql = implode("\n", (new PostgresSchemaCompiler)->compile([
        'tables' => [
            ['id' => 't_orgs', 'name' => 'orgs', 'columns' => [
                ['name' => 'ref', 'type' => 'uuid', 'pk' => true],
            ]],
            ['id' => 't_members', 'name' => 'members', 'columns' => [
                ['name' => 'org_ref', 'type' => 'integer', 'fk' => ['table' => 't_orgs', 'column' => 'ref']],
            ]],
        ],
    ]));

    expect($sql)->toContain('"org_ref" UUID');
});

test('a relationship to a non-key column is skipped, reported, and does not coerce the column type', function () {
    $schema = [
        'tables' => [
            ['id' => 't_projects', 'name' => 'projects', 'columns' => [
                ['name' => 'id', 'type' => 'id', 'pk' => true],
                ['name' => 'organization_id', 'type' => 'bigInteger'], // not a PK / unique
            ]],
            ['id' => 't_orgs', 'name' => 'organizations', 'columns' => [
                // points at projects.organization_id, which Postgres cannot reference
                ['name' => 'id', 'type' => 'id', 'pk' => true, 'fk' => [
                    'table' => 't_projects', 'column' => 'organization_id', 'onDelete' => 'cascade',
                ]],
            ]],
        ],
    ];

    $compiler = new PostgresSchemaCompiler;
    $sql = implode("\n", $compiler->compile($schema));
    $warnings = $compiler->unsupportedRelationships($schema);

    expect($sql)->not->toContain('ADD CONSTRAINT');           // the invalid FK is not emitted
    expect($sql)->toContain('"id" BIGSERIAL');                // organizations.id keeps its own PK type
    expect($warnings)->toHaveCount(1);
    expect($warnings[0])->toContain('organizations.id');
    expect($warnings[0])->toContain('projects.organization_id');
});

test('a relationship to a unique (non-PK) column is allowed', function () {
    $sql = implode("\n", (new PostgresSchemaCompiler)->compile([
        'tables' => [
            ['id' => 't_users', 'name' => 'users', 'columns' => [
                ['name' => 'id', 'type' => 'id', 'pk' => true],
                ['name' => 'email', 'type' => 'string', 'unique' => true],
            ]],
            ['id' => 't_logins', 'name' => 'logins', 'columns' => [
                ['name' => 'email', 'type' => 'string', 'fk' => ['table' => 't_users', 'column' => 'email']],
            ]],
        ],
    ]));

    expect($sql)->toContain('FOREIGN KEY ("email") REFERENCES "users" ("email")');
});

test('a foreign key to an unknown table is skipped', function () {
    $sql = implode("\n", (new PostgresSchemaCompiler)->compile([
        'tables' => [[
            'id' => 't1', 'name' => 'orders',
            'columns' => [
                ['name' => 'ext_id', 'type' => 'integer', 'fk' => ['table' => 'missing', 'column' => 'id']],
            ],
        ]],
    ]));

    expect($sql)->not->toContain('ADD CONSTRAINT');
});

test('an invalid table name throws before any SQL is built', function () {
    expect(fn () => (new PostgresSchemaCompiler)->compile([
        'tables' => [['id' => 't1', 'name' => 'bad name', 'columns' => []]],
    ]))->toThrow(InvalidArgumentException::class);
});
