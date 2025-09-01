<?php

namespace App\Livewire\Video;

use Spatie\Tags\Tag;
use App\Models\Video;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{

    use WithPagination;

    public string $search = '';
    public array $selectedTags = [];
    public array $tags = [];

    protected $queryString = [
        'search' => ['except' => ''],
        // 'selectedTags' rimosso dalla query string
    ];

    public function mount()
    {
        $this->tags = Tag::withType('videos')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('taggables')
                    ->whereColumn('tags.id', 'taggables.tag_id')
                    ->where('taggables.taggable_type', '=', Video::class);
            })
            ->pluck('name')
            ->toArray();

        $this->search = request()->query('search', $this->search);
        // Inizializza $selectedTags come array vuoto
        $this->selectedTags = [];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedTags()
    {
        $this->resetPage();
    }

    public function applyFilters()
    {
        $this->resetPage();
    }


    public function render()
    {
        /** @var App\Models\User */
        $user = Auth::user();

        $videos = Video::query()
            ->when(!empty($this->selectedTags), function ($query) {
                $query->withAnyTags($this->selectedTags, 'videos');
            })
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%');
            })
            ->with(['tags'])
            ->paginate(10);

        return view('livewire.video.index', [
            'videos' => $videos,
            'user' => $user,
        ]);
    }
}
