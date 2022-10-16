<?php

declare(strict_types=1);

namespace practice\arena;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\World;
use practice\kit\KitFactory;
use practice\session\SessionFactory;

final class Arena {

    public function __construct(
        private string $name,
        private string $kit,
        private World $world,
        private array $spawns = [],
        private array $players = [],
        private array $combats = [],
        private array $blocks = []
    ) {
        $world->setTime(World::TIME_MIDNIGHT);
        $world->startTime();
    }

    public function getName(): string {
        return $this->name;
    }

    public function getPlayers(): array {
        return $this->players;
    }

    public function isPlayer(Player $player): bool {
        return isset($this->players[spl_object_hash($player)]);
    }

    public function addPlayer(Player $player): void {
        $this->players[spl_object_hash($player)] = $player;
    }

    public function removePlayer(Player $player): void {
        if (!$this->isPlayer($player)) {
            return;
        }
        unset($this->players[spl_object_hash($player)]);
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
    
    public function handleDamage(EntityDamateEvent $event): void {
        $player = $event->getEntity();
        
        if (!$player instanceof Player) {
            return;
        }
        
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            
            if ($damager instanceof Player) {
                if (!$this->isPlayer($damager)) {
                    $event->cancel();
                    return;
                }
                
                if (isset($this->combats[$player->getName()])) {
                    $combat = $this->combats[$player->getName()];
                    
                    if ($combat['time'] > time() && $combat['player']->getName() !== $damager->getName()) {
                        $event->cancel();
                        return;
                    }
                }
                $this->combats[$player->getName()] = ['time' => time() + 15, 'player' => $damager];
                $this->combats[$damager->getName()] = ['time' => time() + 15, 'player' => $player];
            }
        }
        $finalHealth = $player->getHealth() - $event->getFinalDamage();
        
        if ($finalHealth <= 0.00) {
            $event->cancel();
            
            if (isset($this->combats[$player->getName()])) {
                $combat = $this->combats[$player->getName()];
                $session = SessionFactory::get($combat['player']);
                
            }
            $this->quit($player);
        }
    }

    public function join(Player $player): void {
        $this->addPlayer($player);

        $player->getArmorInventory()->clearAll();
        $player->getInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();

        $player->setHealth($player->getMaxHealth());
        $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());

        $player->getXpManager()->setXpAndProgress(0, 0.0);

        $player->teleport($this->spawns[array_rand($this->spawns)]);

        $kit = KitFactory::get(strtolower($this->kit));
        $kit?->giveTo($player);
    }
    
    public function quit(Player $player): void {
        $session = SessionFactory::get($player);
        
        if ($session === null) {
            return;
        }
        $this->removePlayer($player);
        
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();

        $player->setHealth($player->getMaxHealth());
        $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());

        $player->getXpManager()->setXpAndProgress(0, 0.0);

        $player->teleport($player->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
        
        $session->giveLobyyItems();
        $session->setArena(null);
    }

    public function scoreboard(Player $player): array {
        $time = 0;
        
        $lines = [
            ' &fKills: &c0 &7(0)',
            ' &fDeaths: &c0'
        ];
        
        if (isset($this->combats[$player->getName()])) {
            $combat = $this->combats[$player->getName()];
            
            if ($combat['time'] > time()) {
                $time = $combat['time'] - time();
            }
        }
        $lines[] = '&r&r&r&r';
        $lines[] = ' &fCombat: &c' . $time;
        return $lines;
    }
    
    public function serializeData(): array {
        $data = [
            'kit' => $this->kit,
            'world' => $this->world->getFolderName(),
            'spawns' => []
        ];
        
        foreach ($this->spawns as $spawn) {
            $data['spawns'][] = [
                'x' => $spawn->getX(),
                'y' => $spawn->getY(),
                'z' => $spawn->getZ()
            ];
        }
        
        return $data;
    }
    
    static public function deserializeData(array $data): ?self {
        $storage = [
            'kit' => $data['kit'],
            'spawns' => []
        ];
        
        if (!Server::getInstance()->getWorldManager()->isWorldGenerated($data['world'])) {
            return null;
        }
        
        if (!Server::getInstance()->getWorldManager()->isWorldLoaded($data['world'])) {
            Server::getInstance()->getWorldManager()->loadWorld($data['world']);
        }
        $storage['world'] = Server::getInstance()->getWorldManager()->getWorldByName($data['world']);
        
        foreach ($data['spawns'] as $spawn) {
            $storage['spawns'][] = new Position(floatval($spawn['x']), floatval($spawn['y']), floatval($spawn['z']), $storage['world']);
        }
        return $storage;
    }
}