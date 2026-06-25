<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Category::class);

        return view('admin.categories.index', [
            'categories' => $this->categoryService->getTree(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Category::class);

        return view('admin.categories.create', [
            'categoryOptions' => $this->categoryService->getFlatTreeOptions(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $this->categoryService->create($request->validated(), $request->user());

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category created successfully.');
    }

    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        return view('admin.categories.edit', [
            'category' => $category,
            'categoryOptions' => $this->categoryService->getFlatTreeOptions(),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $this->categoryService->update($category, $request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $this->categoryService->delete($category);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category deleted successfully.');
    }
}
