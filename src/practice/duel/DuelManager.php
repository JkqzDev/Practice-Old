<?php

declare(strict_types=1);

namespace practice\duel;

use practice\duel\queue\PlayerQueue;
use pocketmine\player\Player;

class DuelManager {
    
    public function __construct(
        private array $duels = [],
        private array $queues = []
    ) {}
    
    public function getQueue(Player|string $player): ?PlayerQueue {
        $xuid = $player instanceof Player ? $player->getXuid() : $player;
        
        return $this->queues[$xuid] ?? null;
    }
    
    public function createQueue(Player|string $player, int $duelType = 0, bool $ranked = false): void {
        $xuid = $player instanceof Player ? $player->getXuid() : $player;
        
        $this->queues[$xuid] = new PlayerQueue($xuid, $duelType, $ranked);
    }
    
    public function removeQueue(Player|string $player): void {
        $xuid = $player instanceof Player ? $player->getXuid() : $player;
        
        if (!isset($this->queues[$xuid])) {
            return;
        }
        unset($this->queues[$xuid]);
    }
}