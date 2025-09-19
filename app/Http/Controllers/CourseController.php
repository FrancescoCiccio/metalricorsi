<?php

namespace App\Http\Controllers;

use Flux\Flux;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function index()
    {
        return view('courses.index');
    }

    public function subscribe(Course $course)
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($user && !$user->courses()->where('courses.id', $course->id)->exists()) {
            $user->courses()->attach($course->id);
            $user->notify(new \App\Notifications\CourseJoinedNotification($course, $user));
        }

        return view('courses.show', compact('course'));
    }

    public function show(Course $course)
    {

        /** @var \App\Models\User */
        $user = Auth::user();

        $isSubscribed = $user ? $user->courses()->where('courses.id', $course->id)->exists() : false;

        // You can load additional data if needed
        return view('courses.show', compact('course', 'isSubscribed'));
    }
}
