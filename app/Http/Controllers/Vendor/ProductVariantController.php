<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\GenerateProductVariantsRequest;
use App\Http\Requests\Vendor\SyncProductVariantsRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductVariantGenerationService;
use App\Services\ProductVariantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ProductVariantController extends Controller
{
    public function __construct(
        private readonly ProductVariantGenerationService $generationService,
        private readonly ProductVariantService $productVariantService,
    ) {
    }

    public function generate(GenerateProductVariantsRequest $request): JsonResponse
    {
        $this->authorize('generate', ProductVariant::class);

        $vendorId = (int) ($request->user()->ownedVendor?->id ?? $request->user()->vendor_id);
        $validated = $request->validated();

        $generated = $this->generationService->generateForVendor(
            $vendorId,
            $validated['attributes'],
            $validated['base_sku'] ?? null,
        );

        return response()->json([
            'variants' => $generated,
        ]);
    }

    public function sync(SyncProductVariantsRequest $request, Product $product): RedirectResponse|JsonResponse
    {
        $this->authorize('sync', [ProductVariant::class, $product]);

        $variants = $this->productVariantService->syncVariants($product, $request->validated('variants'));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Variants synced successfully.',
                'count' => count($variants),
            ]);
        }

        return back()->with('status', 'Variants synced successfully.');
    }
}
