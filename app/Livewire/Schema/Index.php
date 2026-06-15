<?php

namespace App\Livewire\Schema;

use App\Livewire\Actions\Logout;
use App\Models\SchemaProject;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::schematic')]
#[Title('Schemas')]
class Index extends Component
{
    /**
     * Create a blank project and jump straight into the builder.
     */
    public function newProject(): void
    {
        $project = Auth::user()->schemaProjects()->create(['name' => 'Untitled Schema']);

        $this->redirectRoute('schemas.builder', ['project' => $project], navigate: true);
    }

    /**
     * The current user's projects, with table counts and colors for the thumbnails.
     *
     * @return Collection<int, SchemaProject>
     */
    #[Computed]
    public function projects(): Collection
    {
        return Auth::user()->schemaProjects()
            ->withCount('tables')
            ->with(['tables' => fn ($query) => $query->select('id', 'schema_project_id', 'color')])
            ->latest('updated_at')
            ->get();
    }

    /**
     * Toggle the favorite flag on one of the current user's projects.
     */
    public function toggleFavorite(int $id): void
    {
        $project = Auth::user()->schemaProjects()->findOrFail($id);
        $project->update(['favorite' => ! $project->favorite]);

        unset($this->projects);
    }

    /**
     * Log the current user out and send them to the landing page.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.schema.index');
    }
}
