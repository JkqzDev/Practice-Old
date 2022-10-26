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
    
    static public function create(string $name, int $attackCooldown, float $maxHeight, float $horizontalKnockback, float $verticalKnockback, bool $canRevert, array $armorContents = [], array $inventoryContents = [], array $effects = []): void {
        self::$kits[$name] = new Kit($attackCooldown, $maxHeight, $horizontalKnockback, $verticalKnockback, $canRevert, $armorContents, $inventoryContents, $effects);
    }

    static public function loadAll(): void {
        $plugin = Practice::getInstance();
        $config = new Config($plugin->getDataFolder() . 'kits.yml', Config::YAML);
        
        foreach ($config->getAll() as $name => $data) {
            $kitData = Kit::deserializeData($data);
            
            self::create($name, $kitData['attackCooldown'], $kitData['maxHeight'], $kitData['horizontalKnockback'], $kitData['verticalKnockback'], $kitData['canRevert'], $kitData['armorContents'], $kitData['inventoryContents'], $kitData['effects']);
        }
    }
    
    static public function saveAll(): void {
        $plugin = Practice::getInstance();
        $config = new Config($plugin->getDataFolder() . 'kits.yml', Config::YAML);
        $kits = $config->getAll();
        
        foreach ($config->getAll() as $name => $data) {
            $kit = self::get($name);
            
            if ($kit !== null) {
                $kitData = $kit->serializeData();
                
                $data['attackCooldown'] = $kitData['attackCooldown'];
                $data['maxHeight'] = $kitData['maxHeight'];
                $data['horizontalKnockback'] = $kitData['horizontalKnockback'];
                $data['verticalKnockback'] = $kitData['verticalKnockback'];
                $data['canRevert'] = $kitData['canRevert'];
            }
            $kits[$name] = $data;
        }
        $config->setAll($kits);
        $config->save();
    }
}