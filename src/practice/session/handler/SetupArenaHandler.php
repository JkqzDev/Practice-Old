<?php

declare(strict_types=1);

namespace practice\session\handler;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use practice\arena\ArenaFactory;
use practice\session\SessionFactory;

final class SetupArenaHandler {
    
    public function __construct(
        private string $name = '',
        private array $spawns = [],
        private ?string $kit = null,
        private ?string $world = null
    ) {}

    public function getWorld(): ?string {
        return $this->world;
    }

    public function getKit(): ?string {
        return $this->kit;
    }

    private function existSpawn(Position $position): bool {
        return isset($this->spawns[$position->__toString()]);
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setWorld(string $world): void {
        $this->world = $world;
    }

    public function setKit(string $kit): void {
        $this->kit = $kit;
    }

    private function addSpawn(Position $position): void {
        $this->spawns[$position->__toString()] = $position;
    }

    private function deleteSpawns(): void {
        $this->spawns = [];
    }

    private function create(Player $player): void {
        $server = Server::getInstance();
        $name = $this->name;
        
        if ($this->world === null) {
            $player->sendMessage(TextFormat::colorize('&cWorld is null'));
            return;
        }
        $world = $server->getWorldManager()->getWorldByName($this->world);

        if ($this->kit === null) {
            $player->sendMessage(TextFormat::colorize('&cKit is null'));
            return;
        }

        if (ArenaFactory::get($name) !== null) {
            $player->sendMessage(TextFormat::colorize('&cArena already exists!'));
            $this->finalizeCreator($player);
            return;
        }
        $kit = $this->kit;
        $spawns = $this->spawns;

        if (count($spawns) === 0) {
            $player->sendMessage(TextFormat::colorize('&cYou can\'t create the arena without spawns.'));
            return;
        }
        ArenaFactory::create($name, $kit, $world, $spawns);
        
        $this->finalizeCreator($player);
        $player->sendMessage(TextFormat::colorize('&aArena ' . $name . ' successfully created'));
    }
    
    public function prepareCreator(Player $player): void {
        $server = Server::getInstance();

        if ($this->world === null) {
            return;
        }
        $world = $server->getWorldManager()->getWorldByName($this->world);
        
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();
        
        $player->teleport($world->getSpawnLocation());
        
        $player->setGamemode(GameMode::CREATIVE());
        
        $selectSpawns = BlockFactory::getInstance()->get(BlockLegacyIds::DIAMOND_ORE, 0)->asItem();
        $selectSpawns->getNamedTag()->setString('practice_item', 'selectSpawns');
        
        $deleteSpawns = BlockFactory::getInstance()->get(BlockLegacyIds::GOLD_ORE, 0)->asItem();
        $deleteSpawns->getNamedTag()->setString('practice_item', 'deleteSpawns');
        
        $save = ItemFactory::getInstance()->get(ItemIds::DYE, 10);
        $save->getNamedTag()->setString('practice_item', 'save');
        
        $cancel = ItemFactory::getInstance()->get(ItemIds::DYE, 1);
        $cancel->getNamedTag()->setString('practice_item', 'cancel');
        
        $player->getInventory()->setContents([
            0 => $selectSpawns,
            1 => $deleteSpawns,
            8 => $save,
            7 => $cancel
        ]);

        $player->sendMessage(TextFormat::colorize('&aNow you have setup arena mode'));
    }
    
    public function finalizeCreator(Player $player): void {
        $session = SessionFactory::get($player);
        
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();
        
        $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
        $player->setGamemode(GameMode::SURVIVAL());
        
        $session->giveLobyyItems();
        $session->stopSetupArenaHandler();
    }
    
    public function handleInteract(PlayerInteractEvent $event): void {
        $block = $event->getBlock();
        $item = $event->getItem();
        $player = $event->getPlayer();
        
        $position = $block->getPosition();
        
        if ($item->getId() === BlockLegacyIds::DIAMOND_ORE) {
            $event->cancel();
            
            if ($this->world === null) {
                return;
            }
            $world = $this->world;
            
            if ($this->existSpawn($position)) {
                $player->sendMessage(TextFormat::colorize('&cSpawn already exists!'));
                return;
            }

            if ($position->getWorld()->getFolderName() !== $world) {
                $player->sendMessage(TextFormat::colorize('&cYou can\'t add a spawn in another world'));
                return;
            }
            $this->addSpawn(Position::fromObject($position->add(0, 1, 0), $position->getWorld()));
            $player->sendMessage(TextFormat::colorize('&aYou have added a new spawn'));
        } elseif ($item->getId() === BlockLegacyIds::GOLD_ORE) {
            $event->cancel();
            
            $this->deleteSpawns();
            $player->sendMessage(TextFormat::colorize('&cYou have removed all spawns'));
        } elseif ($item->getId() === ItemIds::DYE && $item->getMeta() === 10) {
            $event->cancel();
            
            $this->create($player);
        } elseif ($item->getId() === ItemIds::DYE && $item->getMeta() === 1) {
            $event->cancel();
            
            $this->finalizeCreator($player);
            $player->sendMessage(TextFormat::colorize('&cArena creator was cancelled'));
        }
    }
}