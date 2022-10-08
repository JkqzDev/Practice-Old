<?php

declare(strict_types=1);

namespace practice\session;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use practice\arena\Arena;
use practice\duel\Duel;
use practice\duel\queue\PlayerQueue;
use practice\item\arena\JoinArenaItem;
use practice\item\duel\RankedQueueItem;
use practice\item\duel\UnrankedQueueItem;
use practice\Practice;
use practice\session\scoreboard\ScoreboardBuilder;

class Session {
    
    static public function create(string $uuid, string $xuid): self {
        return new self($uuid, $xuid);
    }
    
    private ScoreboardBuilder $scoreboard;
    
    public function __construct(
        private string $uuid,
        private string $xuid,
        private ?Arena $arena = null,
        private ?PlayerQueue $queue = null,
        private ?Duel $duel = null
    ) {
        $this->scoreboard = new ScoreboardBuilder($this, '&l&cMisty Practice');
    }
    
    public function getXuid(): string {
        return $this->xuid;
    }
    
    public function getPlayer(): ?Player {
        return Server::getInstance()->getPlayerByRawUUID($this->uuid);
    }
    
    public function getArena(): ?Arena {
        return $this->arena;
    }

    public function getQueue(): ?PlayerQueue {
        return $this->queue;
    }
    
    public function getDuel(): ?Duel {
        return $this->duel;
    }
    
    public function inArena(): bool {
        return $this->arena !== null;
    }
    
    public function inQueue(): bool {
        return $this->queue !== null;
    }
    
    public function inDuel(): bool {
        return $this->duel !== null;
    }

    public function inLobby(): bool {
        return !$this->inArena() && !$this->inDuel();
    }
    
    public function setArena(?Arena $arena): void {
        $this->arena = $arena;
    }
    
    public function setQueue(?PlayerQueue $queue): void {
        $this->queue = $queue;
    }
    
    public function setDuel(?Duel $duel): void {
        $this->duel = $duel;
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
        $this->arena = null;
        $this->queue = null;
        $this->duel = null;
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