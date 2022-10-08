<?php

declare(strict_types=1);

namespace practice\session\handler;

final class SetupArenaHandler {
    
    public function __construct(
        private array $spawns = [],
        private ?string $world = null
    ) {}
}