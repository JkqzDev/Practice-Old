<?php

declare(strict_types=1);

namespace practice\arena;

use pocketmine\player\Player;
use pocketmine\world\World;
use practice\Practice;

class ArenaManager {

    public function __construct(
        private array $arenas = []
    ) {
        if (Practice::IS_DEVELOPING) {
            $this->createArena('No debuff (test)', 'no_debuff', Practice::getInstance()->getServer()->getWorldManager()->getDefaultWorld());
        }
    }

    public function getArenas(): array {
        return $this->arenas;
    }

    public function createArena(string $name, string $kit, World $world): void {
        $this->arenas[$name] = new Arena($name, $kit, $world);
    }

    public function playerInGame(Player $player): ?Arena {
        foreach ($this->arenas as $arena) {
            if ($arena->isPlayer($player)) {
                return $arena;
            }
        }
        return null;
    }
}