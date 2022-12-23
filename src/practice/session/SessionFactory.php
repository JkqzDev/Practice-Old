<?php

declare(strict_types=1);

namespace practice\session;

use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use practice\Practice;

final class SessionFactory {

    static private array $sessions = [];

    public static function create(Player $player): void {
        $uuid = $player->getUniqueId()->getBytes();
        $xuid = $player->getXuid();
        $name = $player->getName();

        self::$sessions[$xuid] = Session::create($uuid, $xuid, $name);
    }

    public static function remove(string $xuid): void {
        if (self::get($xuid) === null) {
            return;
        }
        unset(self::$sessions[$xuid]);
    }

    public static function get(Player|string $player): ?Session {
        $xuid = $player instanceof Player ? $player->getXuid() : $player;

        return self::$sessions[$xuid] ?? null;
    }

    public static function task(): void {
        Practice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(static function (): void {
            foreach (self::getAll() as $session) {
                $session->update();
            }
        }), 1);
    }

    public static function getAll(): array {
        return self::$sessions;
    }

    public static function loadAll(): void {}

    public static function saveAll(): void {
        foreach (self::getAll() as $session) {
            if ($session->getPlayer() !== null) {
                $session->updatePlayer();
            }
        }
    }
}