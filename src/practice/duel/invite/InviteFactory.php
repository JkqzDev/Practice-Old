<?php

declare(strict_types=1);

namespace practice\duel\invite;

use practice\duel\Duel;
use practice\session\Session;

final class InviteFactory {

    static private array $invites = [];

    static public function create(Session $to, Session $from, int $duelType = Duel::TYPE_NODEBUFF, ?string $worldName = null): void {
        self::$invites[$to->getXuid()][$from->getName()] = new Invite($from, $duelType, $worldName);
    }

    static public function removeFromPlayer(Session $session, Session $target): void {
        if (self::get($session) === null) {
            return;
        }
        $invites = self::get($session);

        if (!isset($invites[$target->getName()])) {
            return;
        }
        unset(self::$invites[$session->getXuid()][$target->getName()]);
    }

    static public function get(Session $session): ?array {
        return self::$invites[$session->getXuid()] ?? null;
    }

    static public function remove(Session $session): void {
        if (self::get($session) === null) {
            return;
        }
        unset(self::$invites[$session->getXuid()]);
    }
}