<?php

declare(strict_types=1);

namespace practice\duel\queue;

use pocketmine\player\Player;
use practice\duel\DuelFactory;
use practice\item\duel\queue\LeaveQueueItem;
use practice\session\SessionFactory;

final class QueueFactory {

    static private array $queues = [];

    public static function create(Player $player, int $duelType = 0, bool $ranked = false): void {
        $xuid = $player->getXuid();
        $session = SessionFactory::get($xuid);

        if ($session === null) {
            return;
        }
        $queue = new PlayerQueue($xuid, $duelType, $ranked);

        $session->setQueue($queue);
        self::$queues[$xuid] = $queue;

        $player->getInventory()->setContents([
            8 => new LeaveQueueItem
        ]);
        $foundQueue = self::found($queue);

        if ($foundQueue !== null) {
            $opponent = $foundQueue->getSession();

            if ($opponent === null) {
                return;
            }
            $opponentPlayer = $opponent->getPlayer();

            if ($opponentPlayer === null) {
                return;
            }
            DuelFactory::create($session, $opponent, $duelType, $ranked);

            self::remove($player);
            self::remove($opponentPlayer);

            $session->setQueue(null);
            $opponent->setQueue(null);
        }
    }

    public static function get(Player $player): ?PlayerQueue {
        $xuid = $player->getXuid();

        return self::$queues[$xuid] ?? null;
    }

    private static function found(PlayerQueue $queue): ?PlayerQueue {
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

    public static function getAll(): array {
        return self::$queues;
    }

    public static function remove(Player $player): void {
        $xuid = $player->getXuid();

        if (self::get($player) === null) {
            return;
        }
        unset(self::$queues[$xuid]);
    }
}