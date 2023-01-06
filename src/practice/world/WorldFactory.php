<?php

declare(strict_types=1);

namespace practice\world;

use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use practice\Practice;

final class WorldFactory {

    static private array $worlds = [];

    public static function getRandom(string $mode): ?World {
        $worlds = self::getAllByMode($mode);

        if (count($worlds) === 0) {
            return null;
        }
        return self::get($worlds[array_rand($worlds)]);
    }

    public static function getAllByMode(string $mode): array {
        $worlds = array_filter(self::getAll(),
            static function (World $world) use ($mode): bool {
                return $world->isMode($mode);
            }
        );

        if (count($worlds) === 0) {
            return [];
        }

        return array_map(static function (World $world) {
            return $world->getName();
        }, $worlds);
    }

    public static function getAll(): array {
        return self::$worlds;
    }

    public static function remove(string $name): void {
        if (self::get($name) === null) {
            return;
        }
        unset(self::$worlds[$name]);
    }

    public static function get(string $world): ?World {
        return self::$worlds[$world] ?? null;
    }

    public static function loadAll(): void {
        $plugin = Practice::getInstance();

        $worldPath = $plugin->getDataFolder() . 'worlds/';
        if (!is_dir($worldPath)) {
            mkdir($worldPath);
        }

        /** @phpstan-ignore-next-line */
        if (Practice::IS_DEVELOPING) {
            /** @var \pocketmine\world\World $world */
            $world = Server::getInstance()->getWorldManager()->getDefaultWorld();

            self::create($world->getFolderName(), ['no debuff'], $world->getSpawnLocation(), $world->getSpawnLocation(), null, null, true);
        }
        $storagePath = $plugin->getDataFolder() . 'storage/';

        if (!is_dir($storagePath)) {
            mkdir($storagePath);
        }
        $config = new Config($plugin->getDataFolder() . 'storage' . DIRECTORY_SEPARATOR . 'worlds.json', Config::JSON);

        foreach ($config->getAll() as $name => $data) {
            $d_data = World::deserializeData($data);

            self::create($name, $d_data['modes'], $d_data['firstPosition'], $d_data['secondPosition'], $d_data['firstPortal'], $d_data['secondPortal']);
        }
    }

    public static function create(string $name, array $modes, Position $firstPosition, Position $secondPosition, ?Position $firstPortal = null, ?Position $secondPortal = null, bool $copy = false): void {
        self::$worlds[$name] = new World($name, $firstPosition, $secondPosition, $modes, $copy, $firstPortal, $secondPortal);
    }

    public static function saveAll(): void {
        $plugin = Practice::getInstance();

        $storagePath = $plugin->getDataFolder() . 'storage/';
        if (!is_dir($storagePath)) {
            mkdir($storagePath);
        }

        $config = new Config($plugin->getDataFolder() . 'storage' . DIRECTORY_SEPARATOR . 'worlds.json', Config::JSON);
        $worlds = [];

        foreach (self::getAll() as $name => $world) {
            $worlds[$name] = $world->serializeData();
        }
        $config->setAll($worlds);
        $config->save();
    }
}