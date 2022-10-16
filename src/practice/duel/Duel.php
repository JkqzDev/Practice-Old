<?php

declare(strict_types=1);

namespace practice\duel;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
use practice\kit\KitFactory;
use practice\Practice;
use practice\session\Session;
use practice\session\SessionFactory;
use practice\world\async\WorldDeleteAsync;
use practice\world\WorldFactory;

class Duel {

    public const TYPE_NODEBUFF = 0;
    public const TYPE_BOXING = 1;
    public const TYPE_BRIDGE = 2;
    public const TYPE_BATTLERUSH = 3;
    public const TYPE_FIST = 4;
    public const TYPE_GAPPLE = 5;
    public const TYPE_SUMO = 6;
    public const TYPE_FINALUHC = 7;
    public const TYPE_CAVEUHC = 8;
    public const TYPE_BUILDUHC = 9;
    public const TYPE_COMBO = 10;
    
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
        protected array $spectators = [],
        protected array $blocks = []
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
    
    public function handleBreak(BlockBreakEvent $event): void {
        $block = $event->getBlock();
        
        if (!isset($this->blocks[$block->getPosition()->__toString()])) {
            $event->cancel();
            return;
        }
        unset($this->blocks[$block->getPosition()->__toString()]);
    }
    
    public function handlePlace(BlockPlaceEvent $event): void {
        $block = $event->getBlock();
        
        $this->blocks[$block->getPosition()->__toString()] = $block;
    }
    
    public function handleDamage(EntityDamageEvent $event): void {
        $player = $event->getEntity();
        
        if (!$player instanceof Player) {
            return;
        }
        $finalHealth = $player->getHealth() - $event->getFinalDamage();
        
        if (!$this->isRunning()) {
            $event->cancel();
            return;
        }
            
        if ($finalHealth <= 0.00) {
            $event->cancel();
            $this->finish($player);
        }
    }
    
    public function prepare(): void {
        $worldName = $this->worldName;
        $world = $this->world;
        
        $firstSession = $this->firstSession;
        $secondSession = $this->secondSession;
        
        $world->setTime(World::TIME_MIDNIGHT);
        $world->stopTime();
        
        $kit = KitFactory::get(strtolower(DuelFactory::getName($this->typeId)));
        
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
            
            $kit?->giveTo($firstPlayer);
            $kit?->giveTo($secondPlayer);
            
            $firstPlayer->teleport(new Position($firstPosition->getX(), $firstPosition->getY(), $firstPosition->getZ(), $world));
            $secondPlayer->teleport(new Position($secondPosition->getX(), $secondPosition->getY(), $secondPosition->getZ(), $world));
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
        $firstPlayer = $this->firstSession->getPlayer();
        $secondPlayer = $this->secondSession->getPlayer();
        
        switch ($this->status) {
            case self::STARTING:
                if ($this->starting <= 0) {
                    $this->status = self::RUNNING;
                    
                    $firstPlayer->sendMessage(TextFormat::colorize('&cMatch started.'));
                    $secondPlayer->sendMessage(TextFormat::colorize('&cMatch started.'));
                    
                    $firstPlayer->sendTitle('Match Started!', TextFormat::colorize('&7The match has begun.'));
                    $secondPlayer->sendTitle('Match Started!', TextFormat::colorize('&7The match has begun.'));
                    return;
                }
                $firstPlayer->sendMessage(TextFormat::colorize('&7The match will be starting in &c' . $this->starting . '&7..'));
                $secondPlayer->sendMessage(TextFormat::colorize('&7The match will be starting in &c' . $this->starting . '&7..'));
                
                $firstPlayer->sendTitle('Match starting', TextFormat::colorize('&7The match will be starting in &c' . $this->starting . '&7..'));
                $secondPlayer->sendTitle('Match starting', TextFormat::colorize('&7The match will be starting in &c' . $this->starting . '&7..'));
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