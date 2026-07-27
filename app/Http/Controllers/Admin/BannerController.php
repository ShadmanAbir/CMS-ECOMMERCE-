<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BannerController extends Controller
{
    protected $uploadService;

    public function __construct(ImageUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function index()
    {
        $banners = Banner::orderBy('created_at', 'desc')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'nullable|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
                'link' => 'nullable|url',
            ]);

            if (!$request->hasFile('image')) {
                return back()->withErrors(['image' => 'No image file received']);
            }

            $imagePath = $this->uploadService->upload($request->file('image'), 'banners');

            Banner::create([
                'title' => $request->title,
                'image' => $imagePath,
                'link' => $request->link,
                'status' => $request->has('status') ? 1 : 0,
            ]);

            $this->flushBannerCache();

            return redirect()->route('admin.banners.index')
                ->with('success', 'Banner created successfully!');
        } catch (\Exception $e) {
            Log::error('Banner upload failed: ' . $e->getMessage());
            return back()->withErrors(['image' => 'Upload failed: ' . $e->getMessage()]);
        }
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'link' => 'nullable|url',
        ]);

        $data = [
            'title' => $request->title,
            'link' => $request->link,
            'status' => $request->has('status') ? 1 : 0,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadService->update(
                $request->file('image'),
                $banner->image,
                'banners'
            );
        }

        $banner->update($data);

        $this->flushBannerCache();

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner updated successfully!');
    }

    public function destroy(Banner $banner)
    {
        $this->uploadService->delete($banner->image);
        $banner->delete();

        $this->flushBannerCache();

        return redirect()->route('admin.banners.index')
            ->with('success', 'Banner deleted successfully!');
    }

    public function toggleStatus(Banner $banner)
    {
        $banner->update(['status' => !$banner->status]);

        $this->flushBannerCache();

        return back()->with('success', 'Banner status updated!');
    }

    protected function flushBannerCache(): void
    {
        $siteSlug = theme('slug', 'default');
        Cache::forget("banners_{$siteSlug}");
    }
}
