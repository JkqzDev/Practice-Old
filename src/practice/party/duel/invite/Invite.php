<?php

declare(strict_types=1);

namespace practice\party\duel\invite;

use practice\party\Party;
use practice\party\PartyFactory;

final class Invite {

    public function __construct(
        private Party $party,
        private int $duelType
    ) {}

    public function getParty(): Party {
        return $this->party;
    }

    public function getDuelType(): int {
        return $this->duelType;
    }

    public function exists(): bool {
        $partyName = $this->party->getName();

        return PartyFactory::get($partyName) !== null;
    }
}