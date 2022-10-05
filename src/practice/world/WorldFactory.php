<?php

declare(strict_types=1);

namespace practice\world;

class WorldFactory {

    static public $worlds = [];

    static public function get(string $name): ?World {
        return self::$worlds[$name] ?? null;
    }
}