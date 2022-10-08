<?php

declare(strict_types=1);

namespace practice\duel;

use pocketmine\scheduler\ClosureTask;
use practice\duel\queue\PlayerQueue;
use practice\duel\type\Nodebuff;
use practice\Practice;

class DuelFactory {
    
    static private array $duels = [];
    
    static public function getAll(): array {
        return $this->duels;
    }
    
    static public function get(int $id): ?Duel {
        return self::$duels[$id] ?? null;
    }
    
    static public function create(PlayerQueue $first, PlayerQueue $second, int $duelType, bool $ranked): void {
        $id = 0;
        
        while (self::get($id) !== null) {
            $id++;
        }
        $word = '';
    }
    
    static public function remove(int $id): void {
        if (self::get($id) === null) {
            return;
        }
        unset(self::$duels[$id]);
    }
    
    static public function getName(int $type): string {
        return match($type) {
            Duel::TYPE_NODEBUFF => 'No Debuff',
            default => 'None'
        };
    }
    
    static public function task(): void {
        Practice::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            foreach (self::getAll() as $duel) {
                $duel->update();
            }
        }), 20);
    }
    
    static private function getClass(int $type): string {
        return match($type) {
            Duel::TYPE_NODEBUFF => Nodebuff::class,
            default => Nodebuff::class
        };
    }
}