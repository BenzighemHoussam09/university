<?php

namespace App\Livewire\Teacher\Modules;

use App\Models\Module;
use App\Models\ModuleCatalog;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class Index extends Component
{
    public string $newName = '';

    public function addFromCatalog(int $catalogId): void
    {
        $catalog = ModuleCatalog::findOrFail($catalogId);

        Module::create([
            'teacher_id' => Auth::guard('teacher')->id(),
            'name' => $catalog->name,
            'created_from_catalog_id' => $catalog->id,
        ]);

        $this->dispatch('module-added');
    }

    public function addCustom(): void
    {
        $this->validate(['newName' => 'required|string|max:160']);

        Module::create([
            'teacher_id' => Auth::guard('teacher')->id(),
            'name' => trim($this->newName),
            'created_from_catalog_id' => null,
        ]);

        $this->newName = '';
        $this->dispatch('module-added');
    }

    public function remove(int $moduleId): void
    {
        $module = Module::findOrFail($moduleId);
        $this->authorize('delete', $module);
        $module->delete();
    }

    public function render(): View
    {
        $myModules = Module::withCount(['groups', 'questions'])->orderBy('name')->get();
        $myModuleNames = $myModules->pluck('name')->map(fn ($n) => strtolower($n))->toArray();

        $catalog = ModuleCatalog::orderBy('name')->get()
            ->filter(fn ($c) => ! in_array(strtolower($c->name), $myModuleNames))
            ->values();

        return view('livewire.teacher.modules.index', compact('myModules', 'catalog'));
    }
}
