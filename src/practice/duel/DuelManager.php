<?php

declare(strict_types=1);

namespace practice\duel;

class DuelManager {
    
    public function __construct(
        private array $duels = [],
        private array $queues = []
    ) {}
    
    
}