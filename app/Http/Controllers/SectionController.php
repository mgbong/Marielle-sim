<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    // GET /sections
    public function index()
    {
        return response()->json(['sections' => Section::all()], 200);
    }

    // POST /sections
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:sections,name'],
        ]);

        $section = Section::create($validated);

        return response()->json(['message' => 'Section created successfully.', 'section' => $section], 201);
    }

    // GET /sections/{id}
    public function show($id)
    {
        $section = Section::find($id);

        if (!$section) {
            return response()->json(['message' => 'Section not found.'], 404);
        }

        return response()->json(['section' => $section], 200);
    }

    // PUT /sections/{id}
    public function update(Request $request, $id)
    {
        $section = Section::find($id);

        if (!$section) {
            return response()->json(['message' => 'Section not found.'], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'unique:sections,name,' . $id],
        ]);

        $section->update($validated);

        return response()->json(['message' => 'Section updated successfully.', 'section' => $section], 200);
    }

    // DELETE /sections/{id}
    public function destroy($id)
    {
        $section = Section::find($id);

        if (!$section) {
            return response()->json(['message' => 'Section not found.'], 404);
        }

        $section->delete();

        return response()->json(['message' => 'Section deleted successfully.'], 200);
    }
}
