<?php

declare(strict_types=1);

namespace practice\arena;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
use practice\kit\KitFactory;
use practice\session\SessionFactory;
use practice\session\setting\gameplay\AutoRespawn;
use practice\session\setting\Setting;

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
        $world->setTime(World::TIME_NOON);
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

    public function inCombat(Player $player): bool {
        if (isset($this->combats[$player->getName()])) {
            $combat = $this->combats[$player->getName()];
            
            return $combat['time'] >= time();
        }
        return false;
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
    
    public function handleDamage(EntityDamageEvent $event): void {
        $player = $event->getEntity();
        
        if (!$player instanceof Player) {
            return;
        }
        $session = SessionFactory::get($player);
        
        if ($session === null) {
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
                    
                    if ($combat['time'] >= time() && $combat['player']->getName() !== $damager->getName()) {
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
            $session->addDeath();
            $session->resetKillstreak();
            
            if (isset($this->combats[$player->getName()])) {
                $combat = $this->combats[$player->getName()];

                if ($combat['time'] >= time()) {
                    $damager = SessionFactory::get($combat['player']);
                    $damager->addKill();
                    $damager->addKillstreak();
                
                    unset($this->combats[$damager->getName()]);
                
                    Server::getInstance()->broadcastMessage(TextFormat::colorize('&a' . $damager->getName() . ' &2[' . $damager->getKills() . '] &7killed &c' . $player->getName() . ' &4[' . $session->getKills() . ']'));
                }
                unset($this->combats[$player->getName()]);
            }
            $autoRespawn = $session->getSetting(Setting::AUTO_RESPAWN);

            if ($autoRespawn instanceof AutoRespawn && $autoRespawn->isEnabled()) {
                $this->join($player);
                return;
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
    
    public function quit(Player $player, bool $withCombat = true): void {
        $session = SessionFactory::get($player);
        
        if ($session === null) {
            return;
        }
        $this->removePlayer($player);
        
        $player->setGamemode(GameMode::SURVIVAL());
        
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
        
        if ($withCombat) {
            if (isset($this->combats[$player->getName()])) {
                $combat = $this->combats[$player->getName()];

                if ($combat['time'] >= time()) {
                    $damager = SessionFactory::get($combat['player']);
                    $damager->addKill();
                    $damager->addKillstreak();
                
                    unset($this->combats[$damager->getName()]);
                
                    Server::getInstance()->broadcastMessage(TextFormat::colorize('&a' . $damager->getName() . ' &2[' . $damager->getKills() . '] &7killed &c' . $player->getName() . ' &4[' . $session->getKills() . ']'));
                }
                unset($this->combats[$player->getName()]);
            }
        }
    }

    public function scoreboard(Player $player): array {
        $time = 0;
        $session = SessionFactory::get($player);
        
        if ($session === null) {
            return [];
        }
        
        $lines = [
            ' &fKills: &b' . $session->getKills(),
            ' &fDeaths: &b' . $session->getDeaths(),
            ' &fKillstreak: &b' . $session->getKillstreak()
        ];
        
        if (isset($this->combats[$player->getName()])) {
            $combat = $this->combats[$player->getName()];
            
            if ($combat['time'] > time()) {
                $time = $combat['time'] - time();
            }
        }
        $lines[] = '&r&r&r&r';
        $lines[] = ' &fCombat: &7' . $time . 's';
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
    
    static public function deserializeData(array $data): ?array {
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