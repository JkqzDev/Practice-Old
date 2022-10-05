<?php

declare(strict_types=1);

namespace practice\duel;

use practice\duel\queue\PlayerQueue;
use pocketmine\player\Player;
use practice\duel\type\Nodebuff;
use practice\item\duel\LeaveQueueItem;
use practice\session\Session;
use practice\session\SessionFactory;

class DuelManager {
    
    public function __construct(
        private array $duels = [],
        private array $queues = []
    ) {
    }

    public function getQueues(): array {
        return $this->queues;
    }

    public function getQueuesById(int $type, bool $ranked = false): array {
        return array_filter($this->queues, function (PlayerQueue $queue) use ($type, $ranked): bool {
            return $queue->getDuelType() === $type && $queue->isRanked() === $ranked;
        });
    }
    
    public function getQueue(Player|string $player): ?PlayerQueue {
        $xuid = $player instanceof Player ? $player->getXuid() : $player;
        
        return $this->queues[$xuid] ?? null;
    }
    
    public function createQueue(Player|string $player, int $duelType = 0, bool $ranked = false): void {
        $xuid = $player instanceof Player ? $player->getXuid() : $player;
        $session = SessionFactory::get($xuid);

        if ($session === null) {
            return;
        }
        $this->queues[$xuid] = new PlayerQueue($xuid, $duelType, $ranked);

        if ($player instanceof Player) {
            $player->getInventory()->setContents([
                new LeaveQueueItem
            ]);
            $session->setState(Session::QUEUE);
        }
    }
    
    public function removeQueue(Player|string $player): void {
        $xuid = $player instanceof Player ? $player->getXuid() : $player;
        
        if (!isset($this->queues[$xuid])) {
            return;
        }
        unset($this->queues[$xuid]);
    }

    public function getDuels(): array {
        return $this->duels;
    }

    static public function getDuelByType(int $type): string {
        return match($type) {
            Duel::TYPE_NODEBUFF => Nodebuff::class,
            default => Nodebuff::class
        };
    }
}