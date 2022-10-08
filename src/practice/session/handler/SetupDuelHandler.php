<?php

declare(strict_types=1);

namespace practice\session\handler;

final class SetupDuelHandler {
    
    public const SETUP_SPAWN = 0;
    public const SETUP_PORTAL = 1;
    
    public function __construct(
        private array $modes = [],
        private bool $withPortal = false,
        private ?Position $firstPosition = null,
        private ?Position $secondPosition = null,
        private ?Position $firstPortal = null,
        private ?Position $secondPortal = null,
        private ?string $world = null
    ) {}
    
    
}