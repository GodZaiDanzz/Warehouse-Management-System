<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display listing of item categories.
     */
    public function index(Request $request)
    {
        $query = Category::withCount('items');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $query->latest()->paginate(10)->withQueryString();
        $totalCategories = Category::count();
        $totalCategorizedItems = \App\Models\Item::whereNotNull('category_id')->count();

        return view('pages.categories.index', [
            'title' => 'Kategori Barang',
            'categories' => $categories,
            'totalCategories' => $totalCategories,
            'totalCategorizedItems' => $totalCategorizedItems,
        ]);
    }

    /**
     * Store a newly created category in database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah terdaftar di sistem.',
        ]);

        try {
            $category = Category::create($validated);

            return redirect()->route('categories.index')->with('success', 'Kategori barang "' . $category->name . '" berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menambah kategori: ' . $e->getMessage());
        }
    }

    /**
     * Update specified category in database.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah terdaftar.',
        ]);

        try {
            $category->update($validated);

            return redirect()->route('categories.index')->with('success', 'Kategori "' . $category->name . '" berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui kategori: ' . $e->getMessage());
        }
    }

    /**
     * Remove specified category from database.
     */
    public function destroy(Category $category)
    {
        try {
            if ($category->items()->count() > 0) {
                return redirect()->back()->with('error', 'Kategori "' . $category->name . '" tidak dapat dihapus karena masih digunakan oleh ' . $category->items()->count() . ' item barang!');
            }

            $name = $category->name;
            $category->delete();

            return redirect()->route('categories.index')->with('success', 'Kategori "' . $name . '" berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }
}
