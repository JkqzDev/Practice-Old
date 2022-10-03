<?php

declare(strict_types=1);

namespace practice\arena;

use pocketmine\player\Player;

class ArenaManager {

    public function __construct(
        private array $arenas = []
    ) {
        
    }

    public function getArenas(): array {
        return $this->arenas;
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