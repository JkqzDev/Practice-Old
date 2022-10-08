<?php

declare(strict_types=1);

namespace practice\duel\queue;

use practice\session\Session;
use practice\session\SessionFactory;

class PlayerQueue {
    
    public function __construct(
        private string $xuid,
        private int $duelType,
        private bool $ranked,
        private int $time = 0,
    ) {
        $this->time = time();
    }

    public function getSession(): ?Session {
        return SessionFactory::get($this->xuid);
    }

    public function getDuelType(): int {
        return $this->duelType;
    }
    
    public function getTime(): int {
        return time() - $this->time;
    }

    public function isRanked(): bool {
        return $this->ranked;
    }
}