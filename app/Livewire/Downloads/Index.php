<?php

namespace App\Livewire\Downloads;

use Spatie\Tags\Tag;
use Livewire\Component;
use App\Models\Download;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public Collection $downloads;
    public Collection $allTags;
    public array $selectedTags = [];

    public function mount()
    {
        $this->allTags = Tag::withType('downloads')->pluck('name')->unique(); // restituisce solo i nomi
        $this->loadDownloads();
    }

    public function updatedSelectedTags()
    {
        $this->loadDownloads();
    }

    public function resetFilters()
    {
        $this->selectedTags = [];
        $this->loadDownloads();
    }

    public function loadDownloads()
    {
        /** @var App\Models\User */
        $user = Auth::user();

        $directDownloads = $user->downloads()->with('tags')->get();
        $groupDownloads = $user->groups()->with('downloads.tags')->get()->pluck('downloads')->flatten();

        $downloads = $directDownloads->merge($groupDownloads)->unique('id');

        if (!empty($this->selectedTags)) {
            $downloads = $downloads->filter(function ($download) {
                $tagNames = $download->tags->pluck('name')->toArray();
                return !empty(array_intersect($tagNames, $this->selectedTags));
            });
        }

        $this->downloads = $downloads->values();
    }


    public function render()
    {
        return view('livewire.downloads.index');
    }
}
