<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreProductRequest;
use App\Http\Requests\Vendor\UpdateProductRequest;
use App\Models\Product;
use App\Services\AttributeService;
use App\Services\CategoryService;
use App\Services\ProductVariantService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly CategoryService $categoryService,
        private readonly AttributeService $attributeService,
        private readonly ProductVariantService $productVariantService,
    ) {
        $this->authorizeResource(Product::class, 'product');
    }

    public function index(): View
    {
        $products = $this->productService->paginateForVendor((int) auth()->user()->vendor_id);

        return view('vendor.products.index', [
            'products' => $products,
        ]);
    }

    public function create(): View
    {
        return view('vendor.products.create', [
            'categoryOptions' => $this->categoryService->getFlatTreeOptions(),
            'attributeOptions' => $this->attributeService->getAllForVendor(auth()->user()),
            'existingVariants' => [],
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = $this->productService->create($request->validated(), (int) $request->user()->vendor_id);
        if ($request->filled('variants')) {
            $this->productVariantService->syncVariants($product, $request->validated('variants'));
        }

        return redirect()
            ->route('vendor.products.show', $product)
            ->with('status', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        $product->load(['categories', 'images']);

        return view('vendor.products.show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product): View
    {
        $product->load(['categories', 'images']);

        return view('vendor.products.edit', [
            'product' => $product,
            'categoryOptions' => $this->categoryService->getFlatTreeOptions(),
            'attributeOptions' => $this->attributeService->getAllForVendor(auth()->user()),
            'existingVariants' => collect($this->productVariantService->getForProduct($product))
                ->map(fn ($variant) => [
                    'combination_key' => $variant->combination_key,
                    'sku' => $variant->sku,
                    'price' => $variant->price,
                    'stock_qty' => $variant->stock_qty,
                    'status' => $variant->status->value,
                    'option_snapshot' => $variant->option_snapshot ?? [],
                    'values' => $variant->values->map(fn ($value) => [
                        'attribute_id' => $value->attribute_id,
                        'attribute_value_id' => $value->attribute_value_id,
                    ])->values()->all(),
                ])
                ->values()
                ->all(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->productService->update($product, $request->validated());
        if ($request->filled('variants')) {
            $this->productVariantService->syncVariants($product, $request->validated('variants'));
        }

        return redirect()
            ->route('vendor.products.edit', $product)
            ->with('status', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->productService->delete($product);

        return redirect()
            ->route('vendor.products.index')
            ->with('status', 'Product deleted successfully.');
    }
}
