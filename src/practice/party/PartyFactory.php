<?php

declare(strict_types=1);

namespace practice\party;

final class PartyFactory {
    
    static private array $parties = [];
    
    public static function getAll(): array {
        return self::$parties;
    }
    
    public static function get(string $name): ?Party {
        return self::$parties[$name] ?? null;
    }
    
    public static function create(): void {
    }
    
    public static function remove(string $name): void {
        if (self::get($name) === null) {
            return;
        }
        unset(self::$parties[$name]);
    }
}
