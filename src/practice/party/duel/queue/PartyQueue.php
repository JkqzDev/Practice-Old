<?php

declare(strict_types=1);

namespace practice\party\duel\queue;

use practice\party\Party;

final class PartyQueue {

    public function __construct(
        private Party $party,
        private int $duelType,
        private int $time = 0,
    ) {
        $this->time = time();
    }

    public function getParty(): Party {
        return $this->party;
    }

    public function getDuelType(): int {
        return $this->duelType;
    }
    
    public function getTime(): int {
        return time() - $this->time;
    }
}