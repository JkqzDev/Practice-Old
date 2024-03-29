<?php

declare(strict_types=1);

namespace practice\duel;

use pocketmine\scheduler\ClosureTask;
use pocketmine\world\World;
use practice\duel\type\BattleRush;
use practice\duel\type\Boxing;
use practice\duel\type\Bridge;
use practice\duel\type\BuildUHC;
use practice\duel\type\CaveUHC;
use practice\duel\type\Combo;
use practice\duel\type\FinalUHC;
use practice\duel\type\Fist;
use practice\duel\type\Gapple;
use practice\duel\type\Nodebuff;
use practice\duel\type\Sumo;
use practice\Practice;
use practice\session\Session;
use practice\world\WorldFactory;

final class DuelFactory {

    /** @var Duel[] */
    static private array $duels = [];

    public static function create(Session $first, Session $second, int $duelType, bool $ranked, string $worldName = null): void {
        $id = 0;

        while (self::get($id) !== null || is_dir(Practice::getInstance()->getServer()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR . 'duel-' . $id)) {
            $id++;
        }
        $className = self::getClass($duelType);
        $duelName = self::getName($duelType);

        if ($worldName === null) {
            $newName = explode(' ', $duelName);
            $worldData = WorldFactory::getRandom(strtolower(implode('', $newName)));
        } else {
            $worldData = WorldFactory::get($worldName);
        }

        if ($worldData === null) {
            $first->giveLobbyItems();
            $second->giveLobbyItems();
            return;
        }
        $worldData->copyWorld(
            'duel-' . $id,
            Practice::getInstance()->getServer()->getDataPath() . 'worlds',
            static function (World $world) use ($className, $id, $duelType, $worldData, $ranked, $first, $second): void {
                /** @var Duel $duel */
                $duel = new $className($id, $duelType, $worldData->getName(), $ranked, $first, $second, $world);

                $first->setDuel($duel);
                $second->setDuel($duel);

                self::$duels[$id] = $duel;
            }
        );
    }

    public static function get(int $id): ?Duel {
        return self::$duels[$id] ?? null;
    }

    private static function getClass(int $type): string {
        return match ($type) {
            Duel::TYPE_BATTLERUSH => BattleRush::class,
            Duel::TYPE_BOXING => Boxing::class,
            Duel::TYPE_BRIDGE => Bridge::class,
            Duel::TYPE_BUILDUHC => BuildUHC::class,
            Duel::TYPE_CAVEUHC => CaveUHC::class,
            Duel::TYPE_COMBO => Combo::class,
            Duel::TYPE_FINALUHC => FinalUHC::class,
            Duel::TYPE_FIST => Fist::class,
            Duel::TYPE_GAPPLE => Gapple::class,
            Duel::TYPE_SUMO => Sumo::class,
            default => Nodebuff::class
        };
    }

    public static function getName(int $type): string {
        return match ($type) {
            Duel::TYPE_BATTLERUSH => 'Battle Rush',
            Duel::TYPE_BOXING => 'Boxing',
            Duel::TYPE_BRIDGE => 'Bridge',
            Duel::TYPE_BUILDUHC => 'Build UHC',
            Duel::TYPE_CAVEUHC => 'Cave UHC',
            Duel::TYPE_COMBO => 'Combo',
            Duel::TYPE_FINALUHC => 'Final UHC',
            Duel::TYPE_FIST => 'Fist',
            Duel::TYPE_GAPPLE => 'Gapple',
            Duel::TYPE_HG => 'HG',
            Duel::TYPE_SUMO => 'Sumo',
            Duel::TYPE_SG => 'SG',
            default => 'No Debuff'
        };
    }

    public static function remove(int $id): void {
        if (self::get($id) === null) {
            return;
        }
        unset(self::$duels[$id]);
    }

    public static function task(): void {
        Practice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(static function (): void {
            foreach (self::getAll() as $duel) {
                $duel->update();
            }
        }), 20);
    }

    public static function getAll(): array {
        return self::$duels;
    }

    public static function disable(): void {
        foreach (self::getAll() as $duel) {
            $duel->delete();
        }
    }
}