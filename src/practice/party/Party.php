<?php

declare(strict_types=1);

namespace practice\party;

use pocketmine\player\Player;

class Party {

    public function __construct(
        private Player $owner,
        private bool $open = true,
        private array $members = []
    ) {
        $this->addMemeber($owner);
    }

    public function getOwner(): Player {
        return $this->owner;
    }

    public function getMembers(): array {
        return $this->members;
    }

    public function isOwner(Player $player): bool {
        return $player->getXuid() === $this->owner->getXuid();
    }

    public function isOpen(): bool {
        return $this->open;
    }

    public function isMember(Player $player): bool {
        return isset($this->members[spl_object_hash($player)]);
    }

    public function addMemeber(Player $player): void {
        $this->members[spl_object_hash($player)] = $player;
    }
}