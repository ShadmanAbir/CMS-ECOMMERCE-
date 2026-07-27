<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Services\ImageUploadService;
use App\Services\InventoryService;
use App\Services\ProductSchemaService;
use App\Services\StockAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function __construct(
        protected ImageUploadService $uploadService,
        protected ProductSchemaService $schemaService,
        protected StockAlertService $stockAlerts,
        protected InventoryService $inventory
    ) {}

    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $products = Product::with('category')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'search'));
    }

    public function create()
    {
        $categories = Category::parents()->with('children')->get();
        $schemaAttributes = $this->schemaService->getAttributes();
        return view('admin.products.create', compact('categories', 'schemaAttributes'));
    }

    public function store(StoreProductRequest $request)
    {
        try {
            // Create product
            $product = Product::create($request->productData());

            // Save dynamic attributes
            if ($request->has('attributes')) {
                $this->schemaService->saveProductAttributes($product, $request->input('attributes', []));
            }

            // Upload product images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    if ($file->isValid()) {
                        $imagePath = $this->uploadService->upload($file, 'products');
                        
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => $imagePath,
                            'sort_order' => $index,
                            'is_primary' => $index === 0,
                        ]);
                    }
                }
            }

            return redirect()->route('admin.products.index')
                ->with('success', 'Product created successfully!');
        } catch (\Exception $e) {
            Log::error('Product creation failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create product: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit(Product $product)
    {
        $categories = Category::parents()->with('children')->get();
        $product->load(['images', 'category']);
        $schemaAttributes = $this->schemaService->getAttributes();
        $attributeValues = $this->schemaService->getProductAttributes($product);
        
        return view('admin.products.edit', compact('product', 'categories', 'schemaAttributes', 'attributeValues'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $stockBefore = (int) $product->stock;

            // Update product
            $product->update($request->productData());

            // Log stock movement if the stock level changed
            $stockAfter = (int) $product->fresh()->stock;
            if ($stockAfter !== $stockBefore) {
                $diff = $stockAfter - $stockBefore;
                $type = $diff > 0 ? 'restock' : 'adjustment';
                $this->inventory->logMovement(
                    $product,
                    $type,
                    $diff,
                    $stockBefore,
                    $stockAfter,
                    auth()->id(),
                    $diff > 0 ? 'Admin restock' : 'Admin adjustment',
                );
            }

            // A restock that lifts stock back above the low-stock threshold
            // clears any open inventory alert for this product automatically.
            $this->stockAlerts->resolveIfRestocked($product->fresh());

            // Save dynamic attributes
            if ($request->has('attributes')) {
                $this->schemaService->saveProductAttributes($product, $request->input('attributes', []));
            }

            // Upload new images if provided (adds to existing images)
            if ($request->hasFile('images')) {
                $uploadedCount = 0;
                foreach ($request->file('images') as $file) {
                    if ($file->isValid()) {
                        $imagePath = $this->uploadService->upload($file, 'products');
                        
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => $imagePath,
                            'sort_order' => $product->images->count() + $uploadedCount,
                        ]);
                        $uploadedCount++;
                        Log::info('Added new image: ' . $imagePath);
                    }
                }
                Log::info('Uploaded ' . $uploadedCount . ' new image(s)');
            }

            Log::info('Product updated successfully');

            $this->flushProductCache();

            return redirect()->route('admin.products.index')
                ->with('success', 'Product updated successfully!');
        } catch (\Exception $e) {
            Log::error('Product update failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Product $product)
    {
        // Delete all product images
        foreach ($product->images as $image) {
            $this->uploadService->delete($image->image_path);
            $image->delete();
        }
        
        $product->delete();

        $this->flushProductCache();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully!');
    }

    public function deleteImage(ProductImage $image)
    {
        try {
            $productId = $image->product_id;
            
            Log::info('Deleting image ID: ' . $image->id . ' from product ID: ' . $productId);
            
            // Delete the physical file
            $this->uploadService->delete($image->image_path);
            
            // Delete the database record
            $image->delete();
            
            Log::info('Image deleted successfully');

            return back()->with('success', 'Image deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete image: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete image: ' . $e->getMessage()]);
        }
    }

    protected function flushProductCache(): void
    {
        $siteSlug = theme('slug', 'default');
        Cache::forget("popular_products_{$siteSlug}");
        Cache::forget("featured_products_{$siteSlug}");
        Cache::forget("new_arrivals_{$siteSlug}");
    }
}
