<?php

declare(strict_types=1);

namespace practice\kit;

use practice\Practice;
use pocketmine\utils\Config;

final class KitFactory {

    /** @var Kit[] */
    static private array $kits = [];

    public static function loadAll(): void {
        $plugin = Practice::getInstance();
        $config = new Config($plugin->getDataFolder() . 'kits.yml', Config::YAML);

        foreach ($config->getAll() as $name => $data) {
            $kitData = Kit::deserializeData($data);

            self::create($name, $kitData['attackCooldown'], $kitData['maxHeight'], $kitData['horizontalKnockback'], $kitData['verticalKnockback'], $kitData['canRevert'], $kitData['armorContents'], $kitData['inventoryContents'], $kitData['effects']);
        }
    }

    public static function getAll(): array {
        return self::$kits;
    }

    public static function create(string $name, int $attackCooldown, float $maxHeight, float $horizontalKnockback, float $verticalKnockback, bool $canRevert, array $armorContents = [], array $inventoryContents = [], array $effects = []): void {
        self::$kits[$name] = new Kit($attackCooldown, $maxHeight, $horizontalKnockback, $verticalKnockback, $canRevert, $armorContents, $inventoryContents, $effects);
    }

    public static function saveAll(): void {
        $plugin = Practice::getInstance();
        $config = new Config($plugin->getDataFolder() . 'kits.yml', Config::YAML);
        $kits = [];

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

    public static function get(string $name): ?Kit {
        return self::$kits[$name] ?? null;
    }
}