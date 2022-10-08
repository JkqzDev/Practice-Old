<?php

declare(strict_types=1);

namespace practice\session\handler;

use pocketmine\player\Player;
use pocketmine\world\Position;

final class SetupArenaHandler {
    
    public function __construct(
        private array $spawns = [],
        private ?string $kit = null,
        private ?string $world = null
    ) {}

    public function getWorld(): ?string {
        return $this->world;
    }

    public function getKit(): ?string {
        return $this->kit;
    }

    public function existSpawn(Position $position): bool {
        return isset($this->spawns[$position->__toString()]);
    }

    public function setWorld(string $world): void {
        $this->world = $world;
    }

    public function setKit(string $kit): void {
        $this->kit = $kit;
    }

    public function addSpawn(Position $position): void {
        $this->spawns[$position->__toString()] = $position;
    }

    public function deleteSpawns(): void {
        $this->spawns = [];
    }

    public function create(Player $player): void {
        if ($this->getWorld() === null) {
            return;
        }

        if ($this->getKit() === null) {
            return;
        }
        $spawns = $this->spawns;

        if (count($spawns) === 0) {
            return;
        }
    }
}