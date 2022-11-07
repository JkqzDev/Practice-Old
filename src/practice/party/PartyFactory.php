<?php

declare(strict_types=1);

namespace practice\party;

use practice\session\Session;

final class PartyFactory {
    
    static private array $parties = [];
    
    public static function getAll(): array {
        return self::$parties;
    }
    
    public static function get(string $name): ?Party {
        return self::$parties[$name] ?? null;
    }
    
    public static function create(Session $owner, string $name, bool $open): void {
        self::$parties[$name] = new Party(
            name: $name,
            owner: $owner->getPlayer(),
            open: $open
        );
    }
    
    public static function remove(string $name): void {
        if (self::get($name) === null) {
            return;
        }
        unset(self::$parties[$name]);
    }
}
