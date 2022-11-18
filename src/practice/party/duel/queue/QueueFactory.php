<?php

declare(strict_types=1);

namespace practice\party\duel\queue;

use practice\party\duel\DuelFactory;
use practice\party\Party;

final class QueueFactory {

    static private array $queues = [];

    static public function getAll(): array {
        return self::$queues;
    }

    static public function get(Party $party): ?PartyQueue {
        return self::$queues[spl_object_hash($party)] ?? null;
    }

    static public function create(Party $party, int $duelType = 0): void {
        $queue = new PartyQueue($party, $duelType);

        $party->setQueue($queue);
        $party->giveItems($party->getOwner());

        self::$queues[spl_object_hash($party)] = $queue;
        $foundQueue = self::found($queue);

        if ($foundQueue !== null) {
            $partyOpponent = $foundQueue->getParty();

            DuelFactory::create($party, $partyOpponent, $foundQueue->getDuelType());

            self::remove($party);
            self::remove($partyOpponent);

            $party->setQueue(null);
            $partyOpponent->setQueue(null);
        }
    }

    static public function remove(Party $party): void {
        if (self::get($party) === null) {
            return;
        }
        $party->setQueue(null);
        $party->giveItems($party->getOwner());
        unset(self::$queues[spl_object_hash($party)]);
    }

    static private function found(PartyQueue $queue): ?PartyQueue {
        foreach (self::getAll() as $q) {
            if ($q->getParty()->getName() === $queue->getParty()->getName()) {
                continue;
            }

            if ($q->getDuelType() !== $queue->getDuelType()) {
                continue;
            }
            return $q;
        }
        return null;
    }
}