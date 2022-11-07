<?php

declare(strict_types=1);

namespace practice\party\duel\invite;

use pocketmine\player\Player;
use practice\party\Party;
use practice\party\PartyFactory;

final class Invite {

    public function __construct(
        private Party $party
    ) {
        
    }

    public function getParty(): Party {
        return $this->party;
    }

    public function exists(): bool {
        $partyName = $this->party->getName();

        return PartyFactory::get($partyName) !== null;
    }
}