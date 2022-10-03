<?php

declare(strict_types=1);

namespace practice\arena;

use pocketmine\player\Player;
use pocketmine\world\World;

class Arena {

    public function __construct(
        private string $name,
        private string $kit,
        private World $world,
        private array $players = []
    ) {
        $world->setTime(World::TIME_MIDNIGHT);
        $world->startTime();
    }

    public function getName(): string {
        return $this->name;
    }

    public function getPlayers(): array {
        return $this->players;
    }

    public function isPlayer(Player $player): bool {
        return isset($this->players[spl_object_hash($player)]);
    }

    public function addPlayer(Player $player): void {
        $this->players[spl_object_hash($player)] = $player;
    }

    public function removePlayer(Player $player): void {
        if (!$this->isPlayer($player)) {
            return;
        }
        unset($this->players[spl_object_hash($player)]);
    }

    public function join(Player $player): void {
        $this->addPlayer($player);
    }

    public function scoreboard(): array {
        return [
            ' &fKills: &c0 &7(0)',
            ' &dDeaths: &c0'
        ];
    }
}