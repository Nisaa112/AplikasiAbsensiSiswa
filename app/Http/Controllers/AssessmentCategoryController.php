<?php

namespace App\Http\Controllers;

use App\Models\AssessmentCategory;
use Illuminate\Http\Request;

class AssessmentCategoryController extends Controller
{

    public function index(Request $request) {
        // Pastikan ada ->with('questions') agar datanya tidak null
        $categories = AssessmentCategory::with('questions')->where('is_active', 1)->get();
        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $categories]);
        }

        return view('setup-penilaian.kategori.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:student,employee',
            'description' => 'nullable|string',
        ]);

        // Default is_active adalah true (1)
        $validated['is_active'] = true;

        $status = AssessmentCategory::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'data' => $status], 201);
        }

        return redirect()->route('web.kategori-penilaian.index')->with('success', 'Indikator penilaian berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $category = AssessmentCategory::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:student,employee',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $category->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Berhasil diupdate']);
        }

        return redirect()->route('web.kategori-penilaian.index')->with('success', 'Indikator penilaian berhasil diperbarui');
    }

    public function destroy(Request $request, $id)
    {
        $category = AssessmentCategory::findOrFail($id);
        $category->delete();

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => 'Berhasil dihapus']);
        }

        return redirect()->route('web.kategori-penilaian.index')->with('success', 'Indikator penilaian berhasil dihapus');
    }

    /**
     * Fitur tambahan untuk toggle status aktif/non-aktif via web
     */
    public function toggleStatus(Request $request, $id)
    {
        $category = AssessmentCategory::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();

        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'is_active' => $category->is_active]);
        }

        return redirect()->route('web.kategori-penilaian.index')->with('success', 'Status indikator berhasil diubah');
    }
}