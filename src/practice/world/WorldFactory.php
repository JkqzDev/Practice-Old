<?php

declare(strict_types=1);

namespace practice\world;

class WorldFactory {

    static private array $worlds = [];
    
    static public function getAll(): array {
        return self::$worlds;
    }

    static public function get(string $name): ?World {
        return self::$worlds[$name] ?? null;
    }
    
    static public function create(): void {
    }
    
    static public function remove(): void {
    }

    static public function loadAll(): void {
    }
    
    static public function saveAll(): void {
    }
}