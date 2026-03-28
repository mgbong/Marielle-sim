<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // GET /courses
    public function index()
    {
        return response()->json(['courses' => Course::all()], 200);
    }

    // POST /courses
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:courses,name'],
        ]);

        $course = Course::create($validated);

        return response()->json(['message' => 'Course created successfully.', 'course' => $course], 201);
    }

    // GET /courses/{id}
    public function show($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Course not found.'], 404);
        }

        return response()->json(['course' => $course], 200);
    }

    // PUT /courses/{id}
    public function update(Request $request, $id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Course not found.'], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'unique:courses,name,' . $id],
        ]);

        $course->update($validated);

        return response()->json(['message' => 'Course updated successfully.', 'course' => $course], 200);
    }

    // DELETE /courses/{id}
    public function destroy($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Course not found.'], 404);
        }

        $course->delete();

        return response()->json(['message' => 'Course deleted successfully.'], 200);
    }
}
