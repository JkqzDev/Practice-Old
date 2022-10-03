<?php

declare(strict_types=1);

namespace practice\arena;

class ArenaManager {

    public function __construct(
        private array $arenas = []
    ) {
        
    }

    public function getArenas(): array {
        return $this->arenas;
    }
}