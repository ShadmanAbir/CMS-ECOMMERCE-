<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    protected $uploadService;

    public function __construct(ImageUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function index()
    {
        $categories = Category::with('children')->orderBy('name')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::parents()->get();
        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:150',
                'description' => 'nullable|string',
                'cat_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'parent_id' => 'nullable|exists:categories,id',
                'status' => 'required|in:active,inactive',
            ]);

            $data = [
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'parent_id' => $request->parent_id,
                'status' => $request->status,
            ];

            if ($request->hasFile('cat_image')) {
                if (!$request->file('cat_image')->isValid()) {
                    return back()->withErrors(['cat_image' => 'Invalid image file'])->withInput();
                }
                $data['cat_image'] = $this->uploadService->upload($request->file('cat_image'), 'category');
            }

            Category::create($data);

            $this->flushCategoryCache();

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category created successfully!');
        } catch (\Exception $e) {
            Log::error('Category creation failed: ' . $e->getMessage());
            return back()->withErrors(['cat_image' => 'Failed to upload image: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit(Category $category)
    {
        $parents = Category::parents()->where('id', '!=', $category->id)->get();
        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:150',
                'description' => 'nullable|string',
                'cat_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                'parent_id' => 'nullable|exists:categories,id',
                'status' => 'required|in:active,inactive',
            ], [
                'cat_image.image' => 'The file must be a valid image (JPEG, PNG, JPG, WEBP)',
                'cat_image.mimes' => 'The image must be in JPEG, PNG, JPG, or WEBP format',
                'cat_image.max' => 'The image size must not exceed 2MB',
            ]);

            $data = [
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'parent_id' => $request->parent_id,
                'status' => $request->status,
            ];

            if ($request->hasFile('cat_image')) {
                if (!$request->file('cat_image')->isValid()) {
                    return back()->withErrors(['cat_image' => 'Invalid image file. Please try again.'])->withInput();
                }
                
                $data['cat_image'] = $this->uploadService->update(
                    $request->file('cat_image'),
                    $category->cat_image,
                    'category'
                );
            }

            $category->update($data);

            $this->flushCategoryCache();

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category updated successfully!');
        } catch (\Exception $e) {
            Log::error('Category update failed: ' . $e->getMessage());
            return back()->withErrors(['cat_image' => 'Failed to upload image: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Category $category)
    {
        if ($category->cat_image) {
            $this->uploadService->delete($category->cat_image);
        }
        
        $category->delete();

        $this->flushCategoryCache();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully!');
    }

    protected function flushCategoryCache(): void
    {
        $siteSlug = theme('slug', 'default');
        Cache::forget("nav_categories_{$siteSlug}");
        Cache::forget("parent_categories_{$siteSlug}");
    }
}
