<?php

declare(strict_types=1);

namespace practice\party\duel\invite;

use practice\party\duel\Duel;
use practice\party\Party;

final class InviteFactory {

    static private array $invites = [];

    static public function get(Party $party): ?array {
        return self::$invites[$party->getName()] ?? null;
    }

    static public function create(Party $to, Party $from, int $duelType = Duel::TYPE_NODEBUFF): void {
        self::$invites[$to->getName()][$from->getName()] = new Invite($from, $duelType);
    }

    static public function removeFromParty(Party $party, Party $target): void {
        if (self::get($party) === null) {
            return;
        }
        $invites = self::get($party);

        if (!isset($invites[$target->getName()])) {
            return;
        }
        unset(self::$invites[$party->getName()][$target->getName()]);
    }

    static public function remove(Party $party): void {
        if (self::get($party) === null) {
            return;
        }
        unset(self::$invites[$party->getName()]);
    }
}