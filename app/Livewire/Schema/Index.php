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
     * Whether the "upgrade to add more projects" modal is open.
     */
    public bool $showLimitModal = false;

    /**
     * Create a blank project and jump straight into the builder.
     */
    public function newProject(): void
    {
        if (! Auth::user()->canCreateProject()) {
            $this->showLimitModal = true;

            return;
        }

        $project = Auth::user()->schemaProjects()->create(['name' => 'Untitled Schema']);

        $this->redirectRoute('schemas.builder', ['project' => $project], navigate: true);
    }

    /**
     * The project cap for the current user, or null when unlimited (paid).
     */
    #[Computed]
    public function projectLimit(): ?int
    {
        return Auth::user()->projectLimit();
    }

    /**
     * Whether the current user is subscribed to a paid plan.
     */
    #[Computed]
    public function subscribed(): bool
    {
        return Auth::user()->subscribed();
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
