<?php

declare(strict_types=1);

namespace practice\session;

use practice\session\scoreboard\ScoreboardBuilder;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use practice\item\arena\JoinArenaItem;
use practice\item\duel\RankedQueueItem;
use practice\item\duel\UnrankedQueueItem;
use practice\Practice;

class Session {
    
    public const LOBBY = 0;
    public const QUEUE = 1;
    public const CREATOR = 2;
    public const DUEL = 3;
    public const ARENA = 4;
    
    static public function create(string $uuid): self {
        return new self($uuid);
    }
    
    private ScoreboardBuilder $scoreboard;
    
    public function __construct(
        private string $uuid,
        private int $state = self::LOBBY
    ) {
        $this->scoreboard = new ScoreboardBuilder($this, '&l&cMisty Practice');
    }
    
    public function getPlayer(): ?Player {
        return Server::getInstance()->getPlayerByRawUUID($this->uuid);
    }

    public function inQueue(): bool {
        $player = $this->getPlayer();

        if ($player === null || !$player->isOnline()) {
            return false;
        }
        return Practice::getInstance()->getDuelManager()->getQueue($player) !== null;
    }
    
    public function inArena(): bool {
        return $this->state === self::ARENA;
    }
    
    public function inDuel(): bool {
        return $this->state === self::DUEL;
    }

    public function inLobby(): bool {
        return !$this->inArena() && !$this->inDuel();
    }
    
    public function setState(int $state): void {
        $this->state = $state;
    }
    
    public function update(): void {
        $this->scoreboard->update();
    }
    
    public function join(): void {
        $player = $this->getPlayer();

        if ($player === null) {
            return;
        }
        $this->scoreboard->spawn();

        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();

        $player->setHealth($player->getMaxHealth());
        $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());

        $this->giveLobyyItems();

        $player->teleport($player->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
        $player->setNameTag(TextFormat::colorize('&7' . $player->getName()));
    }

    public function quit(): void {
        $this->state = self::LOBBY;
    }


    public function giveLobyyItems(): void {
        $player = $this->getPlayer();

        $player->getInventory()->setContents([
            0 => new RankedQueueItem,
            1 => new UnrankedQueueItem,
            2 => new JoinArenaItem
        ]);
    }
}