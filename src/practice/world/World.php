<?php

declare(strict_types=1);

namespace practice\world;

use pocketmine\world\Position;

class World {

    public function __construct(
        private Position $firstPosition,
        private Position $secondPosition,
        private array $modes = []
        private bool $copy = false,
        private ?Position $firstPortal = null,
        private ?Position $secondPortal = null
    ) {
        
    }
}