<?php

declare(strict_types=1);

namespace practice\arena;

use pocketmine\utils\Config;
use pocketmine\world\World;
use practice\Practice;

final class ArenaFactory {
    
    static private array $arenas = [];

    static public function getAll(): array {
        return self::$arenas;
    }
    
    static public function get(string $name): ?Arena {
        return self::$arenas[$name] ?? null;
    }

    static public function create(string $name, string $kit, World $world, array $spawns): void {
        self::$arenas[$name] = new Arena($name, $kit, $world, $spawns);
    }

    static public function remove(string $name): void {
        if (self::get($name) === null) {
            return;
        }
        unset(self::$arenas[$name]);
    }
    
    static public function loadAll(): void {
        if (Practice::IS_DEVELOPING) {
            self::create('No debuff (test)', 'no_debuff', Practice::getInstance()->getServer()->getWorldManager()->getDefaultWorld(), [Practice::getInstance()->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation()->asPosition()]);
        }
        $plugin = Practice::getInstance();
        @mkdir($plugin->getDataFolder() . 'storage');
        
        $config = new Config($plugin->getDataFolder() . 'storage' . DIRECTORY_SEPARATOR . 'arenas.json', Config::JSON);
        
        foreach ($config->getAll() as $name => $data) {
            $d_data = Arena::deserializeData($data);
            
            if ($d_data === null) {
                continue;
            }
            self::create($name, $d_data['kit'], $d_data['world'], $d_data['spawns']);
        }
    }
    
    static public function saveAll(): void {
        $plugin = Practice::getInstance();
        @mkdir($plugin->getDataFolder() . 'storage');
        
        $config = new Config($plugin->getDataFolder() . 'storage' . DIRECTORY_SEPARATOR . 'arenas.json', Config::JSON);
        $arenas = $config->getAll();
        
        foreach ($arenas as $name => $arena) {
            $arenas[$name] = $arena->serializeData();
        }
        $config->setAll($arenas);
        $config->save();
    }
}