<?php

declare(strict_types=1);

namespace practice\party;

class PartyFactory {
    
    static private array $parties = [];
    
    static public function getAll(): array {
        return self::$parties;
    }
    
    static public function getParty(string $name): ?Party {
        return self::$parties[$name] ?? null;
    }
}