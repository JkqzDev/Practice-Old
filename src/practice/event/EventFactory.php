<?php

declare(strict_types=1);

namespace practice\event;

final class EventFactory {

    static private array $events = [];

    static public function getAll(): array {
        return self::$events;
    }

    static public function get(string $name): ?Event {
        return self::$events[$name] ?? null;
    }

    static public function create(): void {
        
    }

    static public function remove(string $name): void {
        if (self::get($name) === null) {
            return;
        }
        unset(self::$events[$name]);
    }

    static public function loadAll(): void {

    }

    static public function saveAll():  void {

    }
}