<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Repositories\Contracts\ProductImageRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductImageRepositoryInterface $productImageRepository,
        private readonly SubscriptionLimitService $subscriptionLimitService,
    ) {
    }

    public function paginateForVendor(int $vendorId): LengthAwarePaginator
    {
        $perPage = (int) config('vendor.pagination.products_per_page', 15);

        return $this->productRepository->paginateForVendor($vendorId, $perPage);
    }

    public function create(array $data, int $vendorId): Product
    {
        $this->subscriptionLimitService->assertCanCreateProduct($vendorId);

        return DB::transaction(function () use ($data, $vendorId): Product {
            $product = $this->productRepository->create([
                'vendor_id' => $vendorId,
                'name' => $data['name'],
                'slug' => $this->resolveSlug($data['name']),
                'description' => $data['description'] ?? null,
                'sku' => $data['sku'],
                'price' => $data['price'],
                'stock' => $data['stock'],
                'status' => $data['status'],
            ]);

            $product->categories()->sync($data['category_ids']);
            $this->syncImages($product, $data, true);

            return $product->load(['categories', 'images']);
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data): Product {
            $updatedProduct = $this->productRepository->update($product, [
                'name' => $data['name'],
                'slug' => $this->resolveSlug($data['name'], $product->id),
                'description' => $data['description'] ?? null,
                'sku' => $data['sku'],
                'price' => $data['price'],
                'stock' => $data['stock'],
                'status' => $data['status'],
            ]);

            $updatedProduct->categories()->sync($data['category_ids']);
            $this->syncImages($updatedProduct, $data, false);

            return $updatedProduct->load(['categories', 'images']);
        });
    }

    public function delete(Product $product): bool
    {
        return DB::transaction(function () use ($product): bool {
            $paths = $product->images()->pluck('path')->all();

            $product->categories()->detach();
            $this->productImageRepository->deleteAllForProduct($product);
            $deleted = $this->productRepository->delete($product);

            if ($paths !== []) {
                Storage::disk('public')->delete($paths);
            }

            return $deleted;
        });
    }

    private function syncImages(Product $product, array $data, bool $isCreate): void
    {
        $removeImageIds = $isCreate ? [] : ($data['remove_image_ids'] ?? []);

        if ($removeImageIds !== []) {
            $pathsToDelete = $product->images()
                ->whereIn('id', $removeImageIds)
                ->pluck('path')
                ->all();

            $this->productImageRepository->deleteByIds($product, $removeImageIds);

            if ($pathsToDelete !== []) {
                Storage::disk('public')->delete($pathsToDelete);
            }
        }

        $uploadedImages = $data['images'] ?? [];
        $altTexts = $data['image_alt_texts'] ?? [];
        $incomingBytes = 0;

        foreach ($uploadedImages as $file) {
            if (! ($file instanceof UploadedFile)) {
                continue;
            }

            $incomingBytes += max(0, (int) $file->getSize());
        }

        $this->subscriptionLimitService->assertCanUploadBytes((int) $product->vendor_id, $incomingBytes);

        $nextOrder = ((int) $product->images()->max('sort_order')) + 1;
        $newRows = [];

        foreach ($uploadedImages as $index => $file) {
            if (! ($file instanceof UploadedFile)) {
                continue;
            }

            $newRows[] = [
                'path' => $file->store('products', 'public'),
                'size_bytes' => max(0, (int) $file->getSize()),
                'alt_text' => $altTexts[$index] ?? null,
                'sort_order' => $nextOrder,
                'is_primary' => false,
            ];

            $nextOrder++;
        }

        $createdImages = $this->productImageRepository->createMany($product, $newRows);
        $this->resolvePrimaryImage($product, $createdImages, $data['primary_image'] ?? null);
    }

    private function resolvePrimaryImage(Product $product, $createdImages, ?string $primarySelection): void
    {
        $selectedImage = null;

        if ($primarySelection) {
            [$type, $value] = array_pad(explode(':', $primarySelection, 2), 2, null);

            if ($type === 'existing' && $value !== null) {
                $selectedImage = $product->images()->whereKey((int) $value)->first();
            }

            if ($type === 'new' && $value !== null) {
                $selectedImage = $createdImages->get((int) $value);
            }
        }

        if (! $selectedImage) {
            $selectedImage = $product->images()->orderBy('sort_order')->first();
        }

        if (! $selectedImage instanceof ProductImage) {
            return;
        }

        $this->productImageRepository->clearPrimary($product);
        $this->productImageRepository->setPrimary($selectedImage);
    }

    private function resolveSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'product';

        $slug = $baseSlug;
        $iteration = 2;

        while ($this->productRepository->slugExists($slug, $ignoreId)) {
            $slug = $baseSlug.'-'.$iteration;
            $iteration++;
        }

        return $slug;
    }
}
