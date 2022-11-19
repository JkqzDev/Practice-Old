<?php

declare(strict_types=1);

namespace practice\party\duel\invite;

use practice\party\Party;
use practice\party\PartyFactory;

final class Invite {

    public function __construct(
        private Party $party,
        private int $duelType,
        private int $time = 0
    ) {
        $this->time = time() + 2 * 60;
    }

    public function getParty(): Party {
        return $this->party;
    }

    public function getDuelType(): int {
        return $this->duelType;
    }

    public function isExpired(): bool {
        return $this->time < time();
    }

    public function exists(): bool {
        $partyName = $this->party->getName();

        return PartyFactory::get($partyName) !== null;
    }
}