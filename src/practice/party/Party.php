<?php

declare(strict_types=1);

namespace practice\party;

use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\duel\queue\QueueFactory;
use practice\item\party\PartyDuelItem;
use practice\item\party\PartyDuelLeaveItem;
use practice\item\party\PartyInformationItem;
use practice\item\party\PartyLeaveItem;
use practice\item\party\PartySettingItem;
use practice\party\duel\Duel;
use practice\party\duel\queue\PartyQueue;
use practice\party\duel\queue\QueueFactory as PartyQueueFactory;
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
        $this->addMemeber($owner, false);
    }

    public function getName(): string {
        return $this->name;
    }

    public function getOwner(): Player {
        return $this->owner;
    }

    /**
     * @return Player[]
     */
    public function getMembers(): array {
        return $this->members;
    }

    public function getMaxPlayers(): int {
        return $this->maxPlayers;
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

    public function setOwner(Player $player): void {
        $this->owner = $player;
    }

    public function addMemeber(Player $player, bool $announce = true): void {
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

        $this->giveItems($player);
        $this->members[spl_object_hash($player)] = $player;

        if ($announce) {
            $this->broadcastMessage('&a' . $player->getName() . ' joined the party.');
        }
    }

    public function removeMember(Player $player, bool $announce = true): void {
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

        if ($announce) {
            $this->broadcastMessage('&c' . $player->getName() . ' left the party.');
        }
    }

    public function setMaxPlayers(int $value): void {
        $this->maxPlayers = $value;
    }

    public function setOpen(bool $value): void {
        $this->open = $value;
    }

    public function setQueue(?PartyQueue $queue): void {
        $this->queue = $queue;
    }

    public function setDuel(?Duel $duel): void {
        $this->duel = $duel;
    }

    public function broadcastMessage(string $message): void {
        foreach ($this->members as $member) {
            $member->sendMessage(TextFormat::colorize($message));
        }
    }

    public function broadcastTitle(string $title, string $subTitle = ''): void {
        foreach ($this->members as $member) {
            $member->sendTitle($title, $subTitle);
        }
    }

    public function giveItems(Player $player): void {
        if (!$this->isOwner($player)) {
            $player->getInventory()->setContents([
                7 => new PartyInformationItem,
                8 => new PartyLeaveItem
            ]);
            return;
        }

        if ($this->inQueue()) {
            $player->getInventory()->setContents([
                8 => new PartyDuelLeaveItem,
            ]);
            return;
        }
        $player->getInventory()->setContents([
            0 => new PartyDuelItem,
            7 => new PartySettingItem,
            8 => new PartyLeaveItem
        ]);
    }

    public function disband(bool $announce = true): void {
        if ($this->inQueue()) {
            PartyQueueFactory::remove($this);
        }

        foreach ($this->members as $member) {
            $this->removeMember($member, false);

            if ($announce) {
                $member->sendMessage(TextFormat::colorize('&cThe party has been eliminated!'));
            }
        }
        PartyFactory::remove($this->name);
    }
}