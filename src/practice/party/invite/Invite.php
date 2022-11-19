<?php

declare(strict_types=1);

namespace practice\party\invite;

use practice\party\Party;
use practice\party\PartyFactory;

final class Invite {

    public function __construct(
        private Party $party
    ) {}

    public function getParty(): Party {
        return $this->party;
    }

    public function canJoin(): bool {
        $party = $this->party;

        if ($party->isFull()) {
            return false;
        }
        return true;
    }

    public function exists(): bool {
        $name = $this->party->getName();
        return PartyFactory::get($name) !== null;
    }
}