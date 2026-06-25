<?php

namespace App\Services\Cart\Stores;

use Illuminate\Session\Store;

class SessionCartStore implements CartStoreInterface
{
    public function __construct(
        private readonly Store $session,
    ) {
    }

    public function all(): array
    {
        $items = $this->session->get((string) config('cart.session_key', 'cart.items'), []);

        return is_array($items) ? array_values($items) : [];
    }

    public function put(string $lineKey, array $item): void
    {
        $key = (string) config('cart.session_key', 'cart.items');
        $items = $this->session->get($key, []);
        $items[$lineKey] = $item;

        $this->session->put($key, $items);
    }

    public function remove(string $lineKey): void
    {
        $key = (string) config('cart.session_key', 'cart.items');
        $items = $this->session->get($key, []);
        unset($items[$lineKey]);

        $this->session->put($key, $items);
    }

    public function clear(): void
    {
        $this->session->forget((string) config('cart.session_key', 'cart.items'));
    }
}
