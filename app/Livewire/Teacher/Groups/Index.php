<?php

namespace App\Livewire\Teacher\Groups;

use App\Enums\Level;
use App\Models\Group;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class Index extends Component
{
    public string $name = '';

    public ?int $moduleId = null;

    public string $level = '';

    public bool $showForm = false;

    public function create(): void
    {
        $this->validate([
            'name' => 'required|string|max:80',
            'moduleId' => 'required|integer|exists:modules,id',
            'level' => 'required|in:'.implode(',', array_column(Level::cases(), 'value')),
        ]);

        Group::create([
            'teacher_id' => Auth::guard('teacher')->id(),
            'module_id' => $this->moduleId,
            'level' => $this->level,
            'name' => trim($this->name),
        ]);

        $this->reset(['name', 'moduleId', 'level', 'showForm']);
    }

    public function delete(int $id): void
    {
        $group = Group::findOrFail($id);
        $this->authorize('delete', $group);
        $group->delete();
    }

    public function render(): View
    {
        $groups = Group::with('module')->withCount('students')->orderBy('name')->get();
        $modules = Module::orderBy('name')->get();
        $levels = Level::cases();

        return view('livewire.teacher.groups.index', compact('groups', 'modules', 'levels'));
    }
}
