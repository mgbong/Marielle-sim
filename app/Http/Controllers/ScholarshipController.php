<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use Illuminate\Http\Request;

class ScholarshipController extends Controller
{
    // GET /scholarships
    public function index()
    {
        $scholarships = Scholarship::all();

        return response()->json(['scholarships' => $scholarships], 200);
    }

    // POST /scholarships
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255', 'unique:scholarships,title'],
            'description' => ['nullable', 'string'],
        ]);

        $scholarship = Scholarship::create($validated);

        return response()->json([
            'message'     => 'Scholarship created successfully.',
            'scholarship' => $scholarship,
        ], 201);
    }

    // GET /scholarships/{id}
    public function show($id)
    {
        $scholarship = Scholarship::find($id);

        if (!$scholarship) {
            return response()->json(['message' => 'Scholarship not found.'], 404);
        }

        return response()->json(['scholarship' => $scholarship], 200);
    }

    // PUT /scholarships/{id}
    public function update(Request $request, $id)
    {
        $scholarship = Scholarship::find($id);

        if (!$scholarship) {
            return response()->json(['message' => 'Scholarship not found.'], 404);
        }

        $validated = $request->validate([
            'title'       => ['sometimes', 'string', 'max:255', 'unique:scholarships,title,' . $id],
            'description' => ['nullable', 'string'],
        ]);

        $scholarship->update($validated);

        return response()->json([
            'message'     => 'Scholarship updated successfully.',
            'scholarship' => $scholarship,
        ], 200);
    }

    // DELETE /scholarships/{id}
    public function destroy($id)
    {
        $scholarship = Scholarship::find($id);

        if (!$scholarship) {
            return response()->json(['message' => 'Scholarship not found.'], 404);
        }

        $scholarship->delete();

        return response()->json(['message' => 'Scholarship deleted successfully.'], 200);
    }
}
