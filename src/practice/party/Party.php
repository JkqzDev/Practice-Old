<?php

declare(strict_types=1);

namespace practice\party;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\duel\queue\QueueFactory;
use practice\party\duel\Duel;
use practice\party\duel\queue\PartyQueue;
use practice\session\SessionFactory;

final class Party {

    public const DEFAULT_PLAYERS = 6;
    public const EIGHT_PLAYERS = 8;
    public const TEN_PLAYERS = 10;

    public function __construct(
        private string $name,
        private Player $owner,
        private int $maxPlayers = self::DEFAULT_PLAYERS,
        private bool $open = true,
        private array $members = [],
        private ?PartyQueue $queue = null,
        private ?Duel $duel = null
    ) {
        $this->addMemeber($owner);
    }

    public function getName(): string {
        return $this->name;
    }

    public function getOwner(): Player {
        return $this->owner;
    }

    public function getMembers(): array {
        return $this->members;
    }

    public function getQueue(): ?PartyQueue {
        return $this->queue;
    }

    public function getDuel(): ?Duel {
        return $this->duel;
    }

    public function isOpen(): bool {
        return $this->open;
    }

    public function isFull(): bool {
        return count($this->members) >= $this->maxPlayers;
    }

    public function isOwner(Player $player): bool {
        return $player->getXuid() === $this->owner->getXuid();
    }

    public function isMember(Player $player): bool {
        return isset($this->members[spl_object_hash($player)]);
    }

    public function inQueue(): bool {
        return $this->queue !== null;
    }

    public function inDuel(): bool {
        return $this->duel !== null;
    }

    public function addMemeber(Player $player): void {
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }

        if ($session->inQueue()) {
            QueueFactory::remove($player);
        }
        $session->setParty($this);

        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();

        $this->members[spl_object_hash($player)] = $player;
    }

    public function removeMember(Player $player): void {
        if (!$this->isMember($player)) {
            return;
        }
        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getCraftingGrid()->clearAll();

        $session = SessionFactory::get($player);
        $session?->giveLobyyItems();
        $session?->setParty(null);

        unset($this->members[spl_object_hash($player)]);
    }

    public function setQueue(?PartyQueue $queue): void {
        $this->queue = $queue;
    }

    public function setDuel(?Duel $duel): void {
        $this->duel = $duel;
    }

    public function broadcast(string $message): void {
        foreach ($this->members as $member) {
            $member->sendMessage(TextFormat::colorize($message));
        }
    }

    public function disband(bool $announce = true): void {
        foreach ($this->members as $member) {
            $this->removeMember($member);

            if ($announce) {
                $member->sendMessage(TextFormat::colorize('&cThe party has been eliminated!'));
            }
        }
    }
}