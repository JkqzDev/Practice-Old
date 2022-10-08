<?php

declare(strict_types=1);

namespace practice\kit;

class Kit {

    public function __construct(
        private int $speedKnockback = 10,
        private float $horizontalKnockback = 0.4,
        private float $verticalKnockback = 0.4,
        private array $armorContents = [],
        private array $inventoryContents = [],
        private array $effects = []
    ) {}
}