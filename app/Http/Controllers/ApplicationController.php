<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    // GET /applications
    public function index()
    {
        $applications = Application::with(['student', 'scholarship'])->get();

        return response()->json(['applications' => $applications], 200);
    }

    // POST /applications
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'     => ['required', 'exists:students,id'],
            'scholarship_id' => ['required', 'exists:scholarships,id'],
            'document'       => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        // Prevent duplicate active applications
        $exists = Application::where('student_id', $validated['student_id'])
            ->where('scholarship_id', $validated['scholarship_id'])
            ->whereNotIn('status', [Application::STATUS_REJECTED, Application::STATUS_CANCELLED])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Student already has an active application for this scholarship.',
            ], 422);
        }

        if ($request->hasFile('document')) {
            $validated['document'] = $request->file('document')->store('documents', 'public');
        }

        $validated['status'] = Application::STATUS_PENDING;

        $application = Application::create($validated);

        return response()->json([
            'message'     => 'Application submitted successfully.',
            'application' => $application->load(['student', 'scholarship']),
        ], 201);
    }

    // GET /applications/{id}
    public function show($id)
    {
        $application = Application::with(['student', 'scholarship'])->find($id);

        if (!$application) {
            return response()->json(['message' => 'Application not found.'], 404);
        }

        return response()->json(['application' => $application], 200);
    }

    // PUT /applications/{id}
    public function update(Request $request, $id)
    {
        $application = Application::find($id);

        if (!$application) {
            return response()->json(['message' => 'Application not found.'], 404);
        }

        if ($application->status !== Application::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending applications can be edited.',
            ], 422);
        }

        $validated = $request->validate([
            'student_id'     => ['sometimes', 'exists:students,id'],
            'scholarship_id' => ['sometimes', 'exists:scholarships,id'],
            'document'       => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        if ($request->hasFile('document')) {
            if ($application->document) {
                Storage::disk('public')->delete($application->document);
            }
            $validated['document'] = $request->file('document')->store('documents', 'public');
        }

        $application->update($validated);

        return response()->json([
            'message'     => 'Application updated successfully.',
            'application' => $application->load(['student', 'scholarship']),
        ], 200);
    }

    // DELETE /applications/{id}
    public function destroy($id)
    {
        $application = Application::find($id);

        if (!$application) {
            return response()->json(['message' => 'Application not found.'], 404);
        }

        if ($application->document) {
            Storage::disk('public')->delete($application->document);
        }

        $application->delete();

        return response()->json(['message' => 'Application cancelled successfully.'], 200);
    }

    // POST /applications/{id}/upload
    public function uploadDocument(Request $request, $id)
    {
        $application = Application::find($id);

        if (!$application) {
            return response()->json(['message' => 'Application not found.'], 404);
        }

        $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        if ($application->document) {
            Storage::disk('public')->delete($application->document);
        }

        $application->document = $request->file('document')->store('documents', 'public');
        $application->save();

        return response()->json([
            'message'  => 'Document uploaded successfully.',
            'document' => $application->document,
        ], 200);
    }

    // POST /applications/{id}/verify
    public function verify($id)
    {
        $application = Application::find($id);

        if (!$application) {
            return response()->json(['message' => 'Application not found.'], 404);
        }

        if ($application->status !== Application::STATUS_PENDING) {
            return response()->json(['message' => 'Only pending applications can be verified.'], 422);
        }

        $application->update(['status' => Application::STATUS_VERIFIED]);

        return response()->json([
            'message'     => 'Application verified successfully.',
            'application' => $application->load(['student', 'scholarship']),
        ], 200);
    }

    // POST /applications/{id}/approve
    public function approve($id)
    {
        $application = Application::find($id);

        if (!$application) {
            return response()->json(['message' => 'Application not found.'], 404);
        }

        if ($application->status !== Application::STATUS_VERIFIED) {
            return response()->json(['message' => 'Only verified applications can be approved.'], 422);
        }

        $application->update(['status' => Application::STATUS_APPROVED]);

        return response()->json([
            'message'     => 'Application approved successfully.',
            'application' => $application->load(['student', 'scholarship']),
        ], 200);
    }

    // POST /applications/{id}/reject
    public function reject($id)
    {
        $application = Application::find($id);

        if (!$application) {
            return response()->json(['message' => 'Application not found.'], 404);
        }

        if (in_array($application->status, [Application::STATUS_APPROVED, Application::STATUS_REJECTED])) {
            return response()->json(['message' => 'This application cannot be rejected.'], 422);
        }

        $application->update(['status' => Application::STATUS_REJECTED]);

        return response()->json([
            'message'     => 'Application rejected successfully.',
            'application' => $application->load(['student', 'scholarship']),
        ], 200);
    }
}
