<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        return view('courses.index');
    }

    public function show(Course $course)
    {
        // You can load additional data if needed
        return view('courses.show', compact('course'));
    }
}
