<?php

declare(strict_types=1);

namespace practice\kit;

class Kit {

    public function __construct(
        private array $armorContents = [],
        private array $inventoryContents = [],
        private array $effects = []
    ) {}
}