<?php

namespace App\Livewire\Course;

use Spatie\Tags\Tag;
use App\Models\Course;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedTags = [];
    public $tags = [];

    protected $queryString = [
        'search' => ['except' => ''],
        // 'selectedTags' rimosso dalla query string
    ];

    public function mount()
    {
        $this->tags = Tag::withType('categories')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('taggables')
                    ->whereColumn('tags.id', 'taggables.tag_id')
                    ->where('taggables.taggable_type', '=', Course::class);
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

    public function subscribe($courseId)
    {
        /** @var App\Models\User */
        $user = Auth::user();

        if ($user && !$user->courses()->where('courses.id', $courseId)->exists()) {
            $user->courses()->attach($courseId);
            $this->dispatch('courseSubscribed', $courseId);
        }
    }

    public function render()
    {
        /** @var App\Models\User */
        $user = Auth::user();

        $courses = Course::query()
            ->when(!empty($this->selectedTags), function ($query) {
                $query->withAnyTags($this->selectedTags, 'categories');
            })
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%');
            })
            ->with(['tags'])
            ->paginate(10);

        $subscribedCourseIds = $user ? $user->courses()->pluck('courses.id')->toArray() : [];

        return view('livewire.course.index', [
            'courses' => $courses,
            'subscribedCourseIds' => $subscribedCourseIds,
        ]);
    }
}
