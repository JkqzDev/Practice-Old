<?php

declare(strict_types=1);

namespace practice\party;

final class PartyFactory {
    
    static private array $parties = [];
    
    public static function getAll(): array {
        return self::$parties;
    }
    
    public static function getParty(string $name): ?Party {
        return self::$parties[$name] ?? null;
    }
    
    public static function create(): void {
    }
    
    public static function remove(): void {
    }
}
