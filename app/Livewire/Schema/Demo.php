<?php

namespace App\Livewire\Schema;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Public, sign-in-free sandbox. Renders the same canvas as the real Builder
 * but reads from a static sample schema and never persists — the Save button
 * is swapped for a "Sign up to save" CTA in the shared view (see $demo flag).
 */
#[Layout('layouts::schematic')]
#[Title('Try Schematic')]
class Demo extends Component
{
    /** Tells the shared builder view to render guest (no-account) chrome. */
    public bool $demo = true;

    /**
     * A starter schema so the canvas isn't empty on first load.
     *
     * @return array{name: string, tables: array<int, array<string, mixed>>}
     */
    #[Computed]
    public function schema(): array
    {
        return [
            'name' => 'Blog Platform (demo)',
            'tables' => [
                [
                    'id' => 'users', 'name' => 'users', 'color' => 'blue',
                    'x' => 60, 'y' => 80, 'indexes' => [],
                    'columns' => [
                        ['id' => 'u_id', 'name' => 'id', 'type' => 'id', 'nullable' => false, 'pk' => true, 'unique' => false, 'index' => false, 'default' => '', 'fk' => null],
                        ['id' => 'u_name', 'name' => 'name', 'type' => 'string', 'nullable' => false, 'pk' => false, 'unique' => false, 'index' => false, 'default' => '', 'fk' => null],
                        ['id' => 'u_email', 'name' => 'email', 'type' => 'string', 'nullable' => false, 'pk' => false, 'unique' => true, 'index' => false, 'default' => '', 'fk' => null],
                        ['id' => 'u_created', 'name' => 'created_at', 'type' => 'timestamp', 'nullable' => true, 'pk' => false, 'unique' => false, 'index' => false, 'default' => '', 'fk' => null],
                    ],
                ],
                [
                    'id' => 'posts', 'name' => 'posts', 'color' => 'green',
                    'x' => 470, 'y' => 60, 'indexes' => [],
                    'columns' => [
                        ['id' => 'p_id', 'name' => 'id', 'type' => 'id', 'nullable' => false, 'pk' => true, 'unique' => false, 'index' => false, 'default' => '', 'fk' => null],
                        ['id' => 'p_user', 'name' => 'user_id', 'type' => 'unsignedBigInteger', 'nullable' => false, 'pk' => false, 'unique' => false, 'index' => true, 'default' => '', 'fk' => ['table' => 'users', 'column' => 'id', 'type' => '1:N', 'onDelete' => 'cascade', 'onUpdate' => 'no action']],
                        ['id' => 'p_title', 'name' => 'title', 'type' => 'string', 'nullable' => false, 'pk' => false, 'unique' => false, 'index' => false, 'default' => '', 'fk' => null],
                        ['id' => 'p_body', 'name' => 'body', 'type' => 'text', 'nullable' => false, 'pk' => false, 'unique' => false, 'index' => false, 'default' => '', 'fk' => null],
                        ['id' => 'p_pub', 'name' => 'published_at', 'type' => 'datetime', 'nullable' => true, 'pk' => false, 'unique' => false, 'index' => false, 'default' => '', 'fk' => null],
                    ],
                ],
                [
                    'id' => 'comments', 'name' => 'comments', 'color' => 'purple',
                    'x' => 470, 'y' => 330, 'indexes' => [],
                    'columns' => [
                        ['id' => 'c_id', 'name' => 'id', 'type' => 'id', 'nullable' => false, 'pk' => true, 'unique' => false, 'index' => false, 'default' => '', 'fk' => null],
                        ['id' => 'c_post', 'name' => 'post_id', 'type' => 'unsignedBigInteger', 'nullable' => false, 'pk' => false, 'unique' => false, 'index' => true, 'default' => '', 'fk' => ['table' => 'posts', 'column' => 'id', 'type' => '1:N', 'onDelete' => 'cascade', 'onUpdate' => 'no action']],
                        ['id' => 'c_user', 'name' => 'user_id', 'type' => 'unsignedBigInteger', 'nullable' => true, 'pk' => false, 'unique' => false, 'index' => true, 'default' => '', 'fk' => ['table' => 'users', 'column' => 'id', 'type' => '1:N', 'onDelete' => 'set null', 'onUpdate' => 'no action']],
                        ['id' => 'c_body', 'name' => 'body', 'type' => 'text', 'nullable' => false, 'pk' => false, 'unique' => false, 'index' => false, 'default' => '', 'fk' => null],
                    ],
                ],
            ],
        ];
    }

    public function render(): View
    {
        return view('livewire.schema.builder');
    }
}
