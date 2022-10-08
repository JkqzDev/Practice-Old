<?php

declare(strict_types=1);

namespace practice\duel;

use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\World;
use practice\Practice;
use practice\session\Session;
use practice\session\SessionFactory;
use practice\world\async\WorldDeleteAsync;
use practice\world\WorldFactory;

class Duel {

    public const TYPE_NODEBUFF = 0;
    
    public const STARTING = 0;
    public const RUNNING = 2;
    public const RESTARTING = 3;

    public function __construct(
        protected int $id,
        protected int $typeId,
        protected string $worldName,
        protected bool $ranked,
        protected Session $firstSession,
        protected Session $secondSession,
        protected World $world,
        protected int $status = self::STARTING,
        protected int $starting = 5,
        protected int $running = 0,
        protected int $restarting = 5,
        protected string $winner = '',
        protected string $loser = '',
        protected array $spectators = []
    ) {
        $this->prepare();
    }

    protected function init(): void {}

    public function getId(): int {
        return $this->id;
    }

    public function getTypeId(): int {
        return $this->typeId;
    }
    
    public function getOpponent(Player $player): ?Player {
        $firstSession = $this->firstSession;
        $secondSession = $this->secondSession;
        
        if ($firstSession->getXuid() === $player->getXuid()) {
            $opponent = $secondSession->getPlayer();
            
            return $opponent;
        }
        $opponent = $firstSession->getPlayer();
        
        return $opponent;
    }
    
    public function isRunning(): bool {
        return $this->status === self::RUNNING;
    }
    
    public function isPlayer(Player $player): bool {
        return $this->firstSession->getXuid() === $player->getXuid() || $this->secondSession->getXuid() === $player->getXuid();
    }
    
    public function isSpectator(Player $player): bool {
        return isset($this->spectators[spl_object_hash($player)]);
    }
    
    public function scoreboard(Player $player): array {
        switch ($this->status) {
            case self::STARTING:
                return [
                    ' &fMatch starting'
                ];
                
            case self::RESTARTING:
                return [
                    ' &fMatch ended'
                ];
                
            default:
                $opponent = $this->getOpponent($player);
                
                return [
                    ' &fKit: &c' . DuelFactory::getName($this->typeId),
                    ' &fDuration: &c' . gmdate('i:s', $this->running),
                    ' &r&r',
                    ' &fYour ping: &c' . $player->getNetworkSession()->getPing(),
                    ' &fTheir ping: &c' . $opponent->getNetworkSession()->getPing()
                ];
        }
    }
    
    public function addSpectator(Player $player): void {
        $this->spectators[spl_object_hash($player)] = $player;
    }
    
    public function removeSpectator(Player $player): void {
        $hash = spl_object_hash($player);
        
        if (!$this->isSpectator($player)) {
            return;
        }
        unset($this->spectators[$hash]);
    }
    
    public function prepare(): void {
        $worldName = $this->worldName;
        $world = $this->world;
        
        $firstSession = $this->firstSession;
        $secondSession = $this->secondSession;
        
        $world->setTime(World::TIME_MIDNIGHT);
        $world->stopTime();
        
        $worldData = WorldFactory::get($worldName);
        $firstPosition = $worldData->getFirstPosition();
        $secondPosition = $worldData->getSecondPosition();

        $firstPlayer = $firstSession->getPlayer();
        $secondPlayer = $secondSession->getPlayer();
        
        if ($firstPlayer !== null && $secondPlayer !== null) {
            $firstPlayer->getArmorInventory()->clearAll();
            $firstPlayer->getInventory()->clearAll();
            $secondPlayer->getArmorInventory()->clearAll();
            $secondPlayer->getInventory()->clearAll();
            
            $firstPlayer->teleport($firstPosition);
            $secondPlayer->teleport($secondPosition);
        }
    }
    
    public function finish(Player $loser): void {
        $firstSession = $this->firstSession;
        $secondSession = $this->secondSession;
        $this->loser = $loser->getName();
        
        if ($loser->getName() === $firstSession->getName()) {
            $this->winner = $secondSession->getName();
        } else {
            $this->winner = $firstSession->getName();
        }
        $firstPlayer = $firstSession->getPlayer();
        $secondPlayer = $secondSession->getPlayer();
        
        $firstPlayer?->getArmorInventory()->clearAll();
        $firstPlayer?->getInventory()->clearAll();
        $secondPlayer?->getArmorInventory()->clearAll();
        $secondPlayer?->getInventory()->clearAll();
        
        $firstPlayer?->setHealth($firstPlayer->getMaxHealth());
        $secondPlayer?->setHealth($secondPlayer->getMaxHealth());
        
        $this->status = self::RESTARTING;
    }
    
    public function delete(): void {
        Practice::getInstance()->getServer()->getWorldManager()->unloadWorld($this->world);
        Practice::getInstance()->getServer()->getAsyncPool()->submitTask(new WorldDeleteAsync(
            'duel-' . $this->id,
            Practice::getInstance()->getServer()->getDataPath() . 'worlds'
        ));
        DuelFactory::remove($this->id);
    }
    
    public function update(): void {
        switch ($this->status) {
            case self::STARTING:
                if ($this->starting <= 0) {
                    $this->status = self::RUNNING;
                    return;
                }
                $this->starting--;
                break;
                
            case self::RUNNING:
                $this->running++;
                break;
                
            case self::RESTARTING:
                if ($this->restarting <= 0) {
                    $firstSession = $this->firstSession;
                    $secondSession = $this->secondSession;
                    
                    $firstPlayer = $firstSession->getPlayer();
                    $secondPlayer = $secondSession->getPlayer();
                    
                    $firstPlayer?->teleport($firstPlayer->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                    $secondPlayer?->teleport($secondPlayer->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                    
                    $firstSession->giveLobyyItems();
                    $secondSession->giveLobyyItems();
                    
                    $firstSession->setDuel(null);
                    $secondSession->setDuel(null);
                    
                    foreach ($this->spectators as $spectator) {
                        $s_spectator = SessionFactory::get($spectator);
                        $s_spectator->setDuel(null);
                        $s_spectator->giveLobyyItems();
                        
                        $spectator->teleport($spectator->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                    }
                    
                    $this->delete();
                    return;
                }
                $this->restarting--;
                break;
        }
    }
    
    public function log(): void {
    }
}