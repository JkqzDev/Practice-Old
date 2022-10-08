<?php

declare(strict_types=1);

namespace practice\arena;

use pocketmine\player\Player;
use pocketmine\world\World;
use practice\Practice;

class ArenaFactory {
    
    static private array $arenas = [];

    static public function getAll(): array {
        return self::$arenas;
    }
    
    static public function get(string $name): ?Arena {
        return self::$arenas[$name] ?? null;
    }

    static public function create(string $name, string $kit, World $world): void {
        self::$arenas[$name] = new Arena($name, $kit, $world);
    }
    
    static public function loadAll(): void {
        if (Practice::IS_DEVELOPING) {
            self::create('No debuff (test)', 'no_debuff', Practice::getInstance()->getServer()->getWorldManager()->getDefaultWorld());
        }
    }
    
    static public function saveAll(): void {
    }
}