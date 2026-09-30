<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\AdminLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(protected AdminLogger $adminLogger) {}

    public function index(): View
    {
        $categories = Category::withCount(['lostItems', 'foundItems'])->orderBy('name')->get();

        return view('admin.categories.index', ['categories' => $categories]);
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $category = Category::create([
            'name' => $request->validated('name'),
            'slug' => Str::slug($request->validated('name')),
            'description' => $request->validated('description'),
            'is_active' => true,
        ]);

        $this->adminLogger->log($request->user(), 'category.created', $category);

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', ['category' => $category]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update([
            'name' => $request->validated('name'),
            'slug' => Str::slug($request->validated('name')),
            'description' => $request->validated('description'),
        ]);

        $this->adminLogger->log($request->user(), 'category.updated', $category);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function toggleActive(Request $request, Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        $this->adminLogger->log(
            $request->user(),
            $category->is_active ? 'category.activated' : 'category.deactivated',
            $category,
        );

        return back()->with('success', $category->is_active ? 'Category activated.' : 'Category deactivated.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        if ($category->lostItems()->exists() || $category->foundItems()->exists()) {
            return back()->with('error', "Can't delete \"{$category->name}\" - it still has reports linked to it.");
        }

        $this->adminLogger->log($request->user(), 'category.deleted', null, "Deleted category \"{$category->name}\"");

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }
}
