<?php

declare(strict_types=1);

namespace practice\world;

use pocketmine\Server;
use pocketmine\world\Position;
use practice\Practice;

class WorldFactory {

    static private array $worlds = [];
    
    static public function getAll(): array {
        return self::$worlds;
    }

    static public function getAllByMode(string $mode): array {
        $worlds = array_filter(self::getAll(), function (World $world) use ($mode): bool {
            return $world->isMode($mode);
        });

        if (count($worlds) === 0) {
            return [];
        }

        return array_map(function (World $world) {
            return $world->getName();
        }, $worlds);
    }

    static public function get(string $name): ?World {
        return self::$worlds[$name] ?? null;
    }

    static public function getRandom(string $mode): ?World {
        $worlds = array_filter(self::getAll(), function (World $world) use ($mode): bool {
            return $world->isMode($mode);
        });

        if (count($worlds) === 0) {
            return null;
        }
        return array_rand($worlds);
    }
    
    static public function create(string $name, array $modes, Position $firstPosition, Position $secondPosition, ?Position $firstPortal = null, ?Position $secondPortal = null, bool $copy = false): void {
        self::$worlds[$name] = new World($name, $firstPosition, $secondPosition, $modes, $copy, $firstPortal, $secondPortal);
    }
    
    static public function remove(string $name): void {
        if (self::get($name) === null) {
            return;
        }
        unset(self::$worlds[$name]);
    }

    static public function loadAll(): void {
        if (Practice::IS_DEVELOPING) {
            $world = Server::getInstance()->getWorldManager()->getDefaultWorld();

            self::create($world->getFolderName(), ['no debuff'], $world->getSpawnLocation(), $world->getSpawnLocation());
        }
    }
    
    static public function saveAll(): void {
    }
}