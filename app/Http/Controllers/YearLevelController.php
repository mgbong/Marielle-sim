<?php

namespace App\Http\Controllers;

use App\Models\YearLevel;
use Illuminate\Http\Request;

class YearLevelController extends Controller
{
    // GET /year-levels
    public function index()
    {
        return response()->json(['year_levels' => YearLevel::all()], 200);
    }

    // POST /year-levels
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:year_levels,name'],
        ]);

        $yearLevel = YearLevel::create($validated);

        return response()->json(['message' => 'Year level created successfully.', 'year_level' => $yearLevel], 201);
    }

    // GET /year-levels/{id}
    public function show($id)
    {
        $yearLevel = YearLevel::find($id);

        if (!$yearLevel) {
            return response()->json(['message' => 'Year level not found.'], 404);
        }

        return response()->json(['year_level' => $yearLevel], 200);
    }

    // PUT /year-levels/{id}
    public function update(Request $request, $id)
    {
        $yearLevel = YearLevel::find($id);

        if (!$yearLevel) {
            return response()->json(['message' => 'Year level not found.'], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'unique:year_levels,name,' . $id],
        ]);

        $yearLevel->update($validated);

        return response()->json(['message' => 'Year level updated successfully.', 'year_level' => $yearLevel], 200);
    }

    // DELETE /year-levels/{id}
    public function destroy($id)
    {
        $yearLevel = YearLevel::find($id);

        if (!$yearLevel) {
            return response()->json(['message' => 'Year level not found.'], 404);
        }

        $yearLevel->delete();

        return response()->json(['message' => 'Year level deleted successfully.'], 200);
    }
}
