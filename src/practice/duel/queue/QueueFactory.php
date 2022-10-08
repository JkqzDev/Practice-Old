<?php

declare(strict_types=1);

namespace practice\duel\queue;

use pocketmine\player\Player;
use practice\duel\DuelFactory;
use practice\item\duel\LeaveQueueItem;
use practice\session\Session;
use practice\session\SessionFactory;

class QueueFactory {
    
    static private array $queues = [];
    
    static public function getAll(): array {
        return self::$queues;
    }
    
    static public function get(Player $player): ?PlayerQueue {
        $xuid = $player->getXuid();
        
        return self::$queues[$xuid] ?? null;
    }
    
    static public function create(Player $player, int $duelType = 0, bool $ranked = false): void {
        $xuid = $player->getXuid();
        $session = SessionFactory::get($xuid);

        if ($session === null) {
            return;
        }
        $queue = new PlayerQueue($xuid, $duelType, $ranked);
        
        $session->setQueue($queue);
        self::$queues[$xuid] = $queue;
        
        $player->getInventory()->setContents([
            new LeaveQueueItem
        ]);
        $foundQueue = self::found($queue);
        
        if ($foundQueue !== null) {
            $opponent = $foundQueue->getSession();
            DuelFactory::create($session, $opponent, $duelType, $ranked);
            
            self::remove($player);
            self::remove($opponent->getPlayer());
            
            $session->setQueue(null);
            $opponent->setQueue(null);
        }
    }
    
    static public function remove(Player $player): void {
        $xuid = $player->getXuid();
        
        if (self::get($player) === null) {
            return;
        }
        unset(self::$queues[$xuid]);
    }
    
    static private function found(PlayerQueue $queue): ?PlayerQueue {
        foreach (self::getAll() as $q) {
            if ($q->getXuid() === $queue->getXuid()) {
                continue;
            }
            
            if ($q->getDuelType() !== $queue->getDuelType()) {
                continue;
             }
             
             if ($q->isRanked() !== $queue->isRanked()) {
                 continue;
             }
             return $q;
        }
        return null;
    }
}