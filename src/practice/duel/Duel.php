<?php

declare(strict_types=1);

namespace practice\duel;

use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\World;
use practice\session\Session;

class Duel {

    public const TYPE_NODEBUFF = 0;
    
    public const STARTING = 0;
    public const RUNNING = 2;
    public const RESTARTING = 3;

    public function __construct(
        protected int $id,
        protected int $typeId,
        protected string $kit,
        protected bool $ranked,
        protected Session $firstSession,
        protected Session $secondSession,
        protected Position $firstPosition,
        protected Position $secondPosition,
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
    
    public function getOpponent(Player $player): Player {
        $firstSession = $this->firstSession;
        $secondSession = $this->secondSession;
        
        if ($firstSession->getXuid() === $player->getXuid()) {
            $opponent = $secondSession->getPlayer();
            assert($opponent !== null);
            
            return $opponent;
        }
        $opponent = $firstSession->getPlayer();
        assert($opponent !== null);
        
        return $opponent;
    }
    
    public function isPlayer(Player $player): bool {
        return $this->firstSession->getXuid() === $player->getXuid() || $this->secondSession->getXuid() === $player->getXuid();
    }
    
    public function isSpectator(Player $player): bool {
        return isset($this->spectators[spl_object_hash($player)]);
    }
    
    public function scoreboard(Player $player): array {
        $opponent = $this->getOpponent($player);
        
        return [
            ' &fOpponent: &c' . $opponent->getName(),
            ' &fDuration: &c' . gmdate('i:s', $this->running)
        ];
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
        $world = $this->world;
        
        $firstSession = $this->firstSession;
        $secondSession = $this->secondSession;
        
        $firstPosition = $this->firstPosition;
        $secondPosition = $this->secondPosition;
        
        $world->setTime(World::TIME_MIDNIGHT);
        $world->stopTime();
        
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
    
    public function finish(): void {
    }
    
    public function delete(): void {
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
                    return;
                }
                $this->restarting--;
                break;
        }
    }
    
    public function log(): void {
    }
}