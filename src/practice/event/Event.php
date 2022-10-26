<?php

declare(strict_types=1);

namespace practice\event;

use pocketmine\player\Player;
use pocketmine\world\Position;
use practice\event\duel\EventDuel;

class Event {

    public const TYPE_SUMO = 0;
    public const TYPE_NODEBUFF = 1;
    public const TYPE_GAPPLE = 2;

    public const STATUS_WAITING = 0;
    public const STATUS_INPROGRESS = 1;
    public const STATUS_FINAL = 2;

    public const MIN_PLAYERS = 2;
    public const MAX_PLAYERS = 30;

    public function __construct(
        private int $type,
        private Position $spawn,
        private Position $firstPosition,
        private Position $secondPosition,
        private int $status = self::STATUS_WAITING,
        private bool $open = false,
        private array $players = [],
        private array $spectators = [],
        private array $battles = [],
        private ?EventDuel $currentDuel = null
    ) {}

    public function isPlayer(Player $player): bool {
        return isset($this->players[spl_object_hash($player)]);
    }

    public function isSpectator(Player $player): bool {
        return isset($this->players[spl_object_hash($player)]);
    }

    public function canStarting(): bool {
        return count($this->players) < self::MIN_PLAYERS;
    }

    public function isFull(): bool {
        return count($this->players) >= self::MAX_PLAYERS;
    }

    public function addPlayer(Player $player): void {
        $this->players[spl_object_hash($player)] = $player;
    }

    public function addSpectator(Player $player): void {
        $this->spectators[spl_object_hash($player)] = $player;
    }

    public function removePlayer(Player $player): void {
        if (!$this->isPlayer($player)) {
            return;
        }
        unset($this->players[spl_object_hash($player)]);
    }

    public function removeSpectator(Player $player): void {
        if (!$this->isSpectator($player)) {
            return;
        }
        unset($this->spectators[spl_object_hash($player)]);
    }
}