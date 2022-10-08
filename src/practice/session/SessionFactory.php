<?php

declare(strict_types=1);

namespace practice\session;

use practice\Practice;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;

class SessionFactory {
    
    static private array $sessions = [];
    
    static public function getAll(): array {
        return self::$sessions;
    }
    
    static public function get(Player|string $player): ?Session {
        $xuid = $player instanceof Player ? $player->getXuid() : $player;
        
        return self::$sessions[$xuid] ?? null;
    }
    
    static public function create(Player $player): void {
        $uuid = $player->getUniqueId()->getBytes();
        $xuid = $player->getXuid();
        $name = $player->getName();
        
        self::$sessions[$xuid] = Session::create($uuid, $xuid, $name);
    }
    
    static public function remove(string $xuid): void {
        if (self::get($xuid) === null) {
            return;
        }
        unset(self::$sessions[$xuid]);
    }
    
    static public function task(): void {
        Practice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            foreach (self::getAll() as $session) {
                $session->update();
            }
        }), 20);
    }
    
    static public function loadAll(): void {
    }
    
    static public function saveAll(): void {
    }
}