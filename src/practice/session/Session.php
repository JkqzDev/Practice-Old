<?php

declare(strict_types=1);

namespace practice\session;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use practice\arena\Arena;
use practice\duel\Duel;
use practice\duel\queue\QueueFactory;
use practice\duel\queue\PlayerQueue;
use practice\item\arena\JoinArenaItem;
use practice\item\duel\RankedQueueItem;
use practice\item\duel\UnrankedQueueItem;
use practice\Practice;
use practice\session\handler\HandlerTrait;
use practice\session\setting\Setting;
use practice\session\setting\SettingTrait;
use practice\session\scoreboard\ScoreboardBuilder;
use practice\session\scoreboard\ScoreboardTrait;

final class Session {
    use HandlerTrait;
    use SettingTrait;
    use ScoreboardTrait;
    
    static public function create(string $uuid, string $xuid, string $name): self {
        return new self($uuid, $xuid, $name);
    }
    
    public function __construct(
        private string $uuid,
        private string $xuid,
        private string $name,
        private ?Arena $arena = null,
        private ?PlayerQueue $queue = null,
        private ?Duel $duel = null
    ) {
        $this->setSetting(Setting::create());
        $this->setScoreboard(new ScoreboardBuilder($this, 'server.logo'));
    }
    
    public function getXuid(): string {
        return $this->xuid;
    }
    
    public function getName(): string {
        return $this->name;
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
    
    public function setName(string $name): void {
        $this->name = $name;
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
        
        if (Practice::IS_DEVELOPING) {
            QueueFactory::create($player);
        }
    }

    public function quit(): void {
        $player = $this->getPlayer();
        
        if ($this->inQueue()) {
            QueueFactory::remove($player);
        } elseif ($this->inDuel()) {
            $duel = $this->getDuel();
            
            if ($duel->isPlayer($player)) {
                $duel->finish($player);
            } else {
                $duel->removeSpectator($player);
            }
        } elseif ($this->inArena()) {
            $arena = $this->getArena();
            $arena->quit($player);
        }
        $this->arena = null;
        $this->queue = null;
        $this->duel = null;

        $this->stopSetupArenaHandler();
        $this->stopSetupDuelHandler();
    }

    public function giveLobyyItems(): void {
        $player = $this->getPlayer();
        
        if ($player === null) {
            return;
        }
        $player->getInventory()->setContents([
            0 => new RankedQueueItem,
            1 => new UnrankedQueueItem,
            2 => new JoinArenaItem
        ]);
    }
}