<?php

namespace App\Services\Cart\Stores;

interface CartStoreInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array;

    /**
     * @param array<string, mixed> $item
     */
    public function put(string $lineKey, array $item): void;

    public function remove(string $lineKey): void;

    public function clear(): void;
}
