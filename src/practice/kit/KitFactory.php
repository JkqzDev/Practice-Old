<?php

declare(strict_types=1);

namespace practice\kit;

use pocketmine\utils\Config;
use practice\Practice;

final class KitFactory {

    static private array $kits = [];
    
    static public function getAll(): array {
        return self::$kits;
    }
    
    static public function get(string $name): ?Kit {
        return self::$kits[$name] ?? null;
    }
    
    static public function create(string $name, int $attackCooldown, float $horizontalKnockback, float $verticalKnockback, array $armorContents = [], array $inventoryContents = [], array $effects = []): void {
        self::$kits[$name] = new Kit($attackCooldown, $horizontalKnockback, $verticalKnockback, $armorContents, $inventoryContents, $effects);
    }

    static public function loadAll(): void {
        $plugin = Practice::getInstance();
        $config = new Config($plugin->getDataFolder() . 'kits.yml', Config::YAML);
        
        foreach ($config->getAll() as $name => $data) {
            $kitData = Kit::deserializeData($data);
            
            self::create($name, $kitData['attackCooldown'], $kitData['horizontalKnockback'], $kitData['verticalKnockback'], $kitData['armorContents'], $kitData['inventoryContents'], $kitData['effects']);
        }
        var_dump(array_keys(self::getAll()));
    }
    
    static public function saveAll(): void {
        $plugin = Practice::getInstance();
        $config = new Config($plugin->getDataFolder() . 'kits.yml', Config::YAML);
        $kits = [];
        
        foreach ($config->getAll() as $name => $data) {
            $kit = self::get($name);
            
            if ($kit !== null) {
                $kitData = $kit->serializeData();
                
                $data['attackCooldown'] = $kitData['attackCooldown'];
                $data['horizontalKnockback'] = $kitData['horizontalKnockback'];
                $data['verticalKnockback'] = $kitData['verticalKnockback'];
            }
            $kits[$name] = $data;
        }
        $config->setAll($kits);
        $config->save();
    }
}