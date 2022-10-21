<?php

declare(strict_types=1);

namespace practice\session\handler;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use practice\session\SessionFactory;
use practice\world\WorldFactory;

final class SetupDuelHandler {
    
    public function __construct(
        private string $name = '',
        private array $modes = [],
        private bool $withPortal = false,
        private ?Vector3 $firstPosition = null,
        private ?Vector3 $secondPosition = null,
        private ?Vector3 $firstPortal = null,
        private ?Vector3 $secondPortal = null,
        private ?string $world = null
    ) {}
    
    public function addMode(string $mode): void {
        $this->modes[] = $mode;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setWithPortal(bool $withPortal): void {
        $this->withPortal = $withPortal;
    }

    public function setFirstPosition(Vector3 $position): void {
        $this->firstPosition = $position;
    }

    public function setSecondPosition(Vector3 $position): void {
        $this->secondPosition = $position;
    }

    public function setFirstPortal(Vector3 $position): void {
        $this->firstPortal = $position;
    }

    public function setSecondPortal(Vector3 $position): void {
        $this->secondPortal = $position;
    }

    public function setWorld(string $world): void {
        $this->world = $world;
    }

    private function create(Player $player): void {
        $name = $this->name;

        if ($this->firstPosition === null) {
            $player->sendMessage(TextFormat::colorize('&cYou haven\'t added the first position'));
            return;
        }

        if ($this->secondPosition === null) {
            $player->sendMessage(TextFormat::colorize('&cYou haven\'t added the second position'));
            return;
        }

        if ($this->withPortal) {
            if ($this->firstPortal === null) {
                $player->sendMessage(TextFormat::colorize('&cYou haven\'t added the first portal'));
                return;
            }

            if ($this->secondPortal === null) {
                $player->sendMessage(TextFormat::colorize('&cYou haven\'t added the second portal'));
                return;
            }
        }

        if (WorldFactory::get($name) !== null) {
            $player->sendMessage(TextFormat::colorize('&cWorld already exists!'));
            return;
        }
        WorldFactory::create($name, $this->modes, $this->firstPosition, $this->secondPosition, $this->firstPortal, $this->secondPortal, true);
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

        $firstPosition = BlockFactory::getInstance()->get(BlockLegacyIds::DIAMOND_ORE, 0);
        $secondPosition = BlockFactory::getInstance()->get(BlockLegacyIds::GOLD_ORE, 0);

        $save = ItemFactory::getInstance()->get(ItemIds::DYE, 10);
        $cancel = ItemFactory::getInstance()->get(ItemIds::DYE, 1);

        $firstPortal = BlockFactory::getInstance()->get(BlockLegacyIds::LAPIS_ORE, 0);
        $secondPortal = BlockFactory::getInstance()->get(BlockLegacyIds::EMERALD_ORE, 0);

        $player->getInventory()->setContents([
            0 => $firstPosition,
            1 => $secondPosition,
            2 => $firstPortal,
            3 => $secondPortal,
            7 => $cancel,
            8 => $save
        ]);
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
        $session->stopSetupDuelHandler();
    }

    public function handleInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
    }
}