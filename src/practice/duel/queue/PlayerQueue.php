<?php

declare(strict_types=1);

namespace practice\duel\queue;

class PlayerQueue {
    
    public function __construct(
        private string $xuid,
        private int $duelType,
        private bool $ranked
    ) {}
}