<?php

declare(strict_types=1);

namespace practice\kit;

class KitFactory {

    static private array $kits = [];

    static public function get(string $name): ?Kit {
        return self::$kits[$name] ?? null;
    }

    static public function loadAll(): void {
    }
    
    static public function saveAll(): void {
    }
}