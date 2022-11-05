<?php

declare(strict_types=1);

namespace practice\party\duel;

use pocketmine\world\World;
use practice\party\duel\type\Gapple;
use practice\party\duel\type\Nodebuff;
use practice\party\Party;
use practice\Practice;
use practice\world\WorldFactory;

final class DuelFactory {

    static private array $duels = [];

    static public function getAll(): array {
        return self::$duels;
    }

    static public function get(int $id): ?Duel {
        return self::$duels[$id] ?? null;
    }

    static public function create(Party $firstParty, Party $secondParty, int $duelType): void {
        $id = 0;
        
        while (self::get($id) !== null || is_dir(Practice::getInstance()->getServer()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR . 'party-duel-' . $id)) {
            $id++;
        }
        $className = self::getClass($duelType);
        $duelName = self::getName($duelType);

        $newName = explode(' ', $duelName);
        $worldData = WorldFactory::getRandom(strtolower(implode('', $newName)));

        if ($worldData === null) {
            return;
        }
        $worldData->copyWorld(
            'party-duel-' . $id,
            Practice::getInstance()->getServer()->getDataPath() . 'worlds',
            static function (World $world) use ($className, $id, $duelType, $worldData, $firstParty, $secondParty): void {
                $duel = new $className($id, $duelType, $worldData->getName(), $firstParty, $secondParty, $world);
                
                $firstParty->setDuel($duel);
                $secondParty->setDuel($duel);

                self::$duels[$id] = $duel;
            }
        );
    }

    static public function remove(int $id): void {
        if (self::get($id) === null) {
            return;
        }
        unset(self::$duels[$id]);
    }

    static public function getName(int $type): string {
        return match($type) {
            Duel::TYPE_NODEBUFF => 'No Debuff',
            Duel::TYPE_GAPPLE => 'Gapple',
            default => 'None'
        };
    }

    static private function getClass(int $type): string {
        return match($type) {
            Duel::TYPE_NODEBUFF => Nodebuff::class,
            Duel::TYPE_GAPPLE => Gapple::class,
            default => Nodebuff::class
        };
    }
}