<?php

namespace App\Data;

class CartViewData
{
    /**
     * @param array<int, array<string, mixed>> $groups
     * @param array<string, mixed> $totals
     */
    public function __construct(
        public readonly array $groups,
        public readonly array $totals,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'groups' => $this->groups,
            'totals' => $this->totals,
        ];
    }
}
