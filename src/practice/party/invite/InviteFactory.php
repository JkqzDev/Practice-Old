<?php

declare(strict_types=1);

namespace practice\party\invite;

use pocketmine\player\Player;
use practice\party\Party;

final class InviteFactory {

    static private array $invites = [];

    static public function create(Player $player, Party $party): void {
        self::$invites[$player->getXuid()][$party->getName()] = new Invite($party);
    }

    static public function removeFromParty(Player $player, string $partyName): void {
        $invites = self::get($player);

        if ($invites === null) {
            return;
        }

        if (!isset($invites[$partyName])) {
            return;
        }
        unset(self::$invites[$player->getXuid()][$partyName]);
    }

    static public function get(Player $player): ?array {
        return self::$invites[$player->getXuid()] ?? null;
    }

    static public function remove(Player $player): void {
        if (self::get($player) === null) {
            return;
        }
        unset(self::$invites[$player->getXuid()]);
    }
}