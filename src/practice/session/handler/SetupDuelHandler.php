<?php

declare(strict_types=1);

namespace practice\session\handler;

use pocketmine\Server;
use practice\world\World;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use practice\session\Session;
use pocketmine\world\Position;
use pocketmine\player\GameMode;
use pocketmine\item\ItemFactory;
use pocketmine\utils\TextFormat;
use practice\world\WorldFactory;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use practice\session\SessionFactory;
use pocketmine\event\player\PlayerInteractEvent;

final class SetupDuelHandler {

    public function __construct(
        private string    $name = '',
        private array     $modes = [],
        private bool      $withPortal = false,
        private ?Position $firstPosition = null,
        private ?Position $secondPosition = null,
        private ?Position $firstPortal = null,
        private ?Position $secondPortal = null,
    ) {}

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setModes(array $modes): void {
        $this->modes = $modes;
    }

    public function setWithPortal(bool $withPortal): void {
        $this->withPortal = $withPortal;
    }

    public function prepareCreator(Player $player): void {
        $server = Server::getInstance();

        if ($this->name === '') {
            return;
        }
        $world = $server->getWorldManager()->getWorldByName($this->name);

        if (!isset($world)) {
            return;
        }
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();

        $player->teleport($world->getSpawnLocation());
        $player->setGamemode(GameMode::CREATIVE());

        $firstPosition = BlockFactory::getInstance()->get(BlockLegacyIds::DIAMOND_ORE, 0)->asItem();
        $firstPosition->setCustomName(TextFormat::colorize('&gFirst Position'));
        $firstPosition->getNamedTag()->setString('practice_item', 'firstPosition');
        $secondPosition = BlockFactory::getInstance()->get(BlockLegacyIds::GOLD_ORE, 0)->asItem();
        $secondPosition->setCustomName(TextFormat::colorize('&gSecond Position'));
        $secondPosition->getNamedTag()->setString('practice_item', 'secondPosition');

        $save = ItemFactory::getInstance()->get(ItemIds::DYE, 10);
        $save->setCustomName(TextFormat::colorize('&aSave'));
        $save->getNamedTag()->setString('practice_item', 'save');
        $cancel = ItemFactory::getInstance()->get(ItemIds::DYE, 1);
        $cancel->setCustomName(TextFormat::colorize('&cCancel'));
        $cancel->getNamedTag()->setString('practice_item', 'cancel');

        $firstPortal = BlockFactory::getInstance()->get(BlockLegacyIds::LAPIS_ORE, 0)->asItem();
        $firstPortal->setCustomName(TextFormat::colorize('&2First Portal'));
        $firstPortal->getNamedTag()->setString('practice_item', 'firstPortal');
        $secondPortal = BlockFactory::getInstance()->get(BlockLegacyIds::EMERALD_ORE, 0)->asItem();
        $secondPortal->setCustomName(TextFormat::colorize('&2Second Portal'));
        $secondPortal->getNamedTag()->setString('practice_item', 'secondPortal');
        
        if ($this->withPortal) {
            $player->getInventory()->setContents([
                0 => $firstPosition,
                1 => $secondPosition,
                2 => $firstPortal,
                3 => $secondPortal,
                7 => $cancel,
                8 => $save
            ]);
        } else {
            $player->getInventory()->setContents([
                0 => $firstPosition,
                1 => $secondPosition,
                7 => $cancel,
                8 => $save
            ]);
        }
    }

    public function handleInteract(PlayerInteractEvent $event): void {
        $block = $event->getBlock();
        $item = $event->getItem();
        $player = $event->getPlayer();

        $position = $block->getPosition();

        if ($item->getId() === BlockLegacyIds::DIAMOND_ORE) {
            $event->cancel();

            if ($this->name === '') {
                return;
            }
            $world = $this->name;

            if ($position->getWorld()->getFolderName() !== $world) {
                $player->sendMessage(TextFormat::colorize('&cYou can\'t set a first spawn in another world'));
                return;
            }
            $this->setFirstPosition(Position::fromObject($position->add(0, 1, 0), $position->getWorld()));
            $player->sendMessage(TextFormat::colorize('&aYou have set the first position'));

        } elseif ($item->getId() === BlockLegacyIds::GOLD_ORE) {
            $event->cancel();

            if ($this->name === '') {
                return;
            }
            $world = $this->name;

            if ($position->getWorld()->getFolderName() !== $world) {
                $player->sendMessage(TextFormat::colorize('&cYou can\'t set a second spawn in another world'));
                return;
            }
            $this->setSecondPosition(Position::fromObject($position->add(0, 1, 0), $position->getWorld()));
            $player->sendMessage(TextFormat::colorize('&aYou have set the second position'));

        } elseif ($item->getId() === BlockLegacyIds::LAPIS_ORE) {
            $event->cancel();

            if (!$this->withPortal) {
                $player->sendMessage(TextFormat::colorize('&cThis duel world does not contain portals'));
                return;
            }

            if ($this->name === '') {
                return;
            }
            $world = $this->name;

            if ($position->getWorld()->getFolderName() !== $world) {
                $player->sendMessage(TextFormat::colorize('&cYou can\'t set a first portal in another world'));
                return;
            }
            $this->setFirstPortal(Position::fromObject($position->add(0, 1, 0), $position->getWorld()));
            $player->sendMessage(TextFormat::colorize('&aYou have set the first portal'));

        } elseif ($item->getId() === BlockLegacyIds::EMERALD_ORE) {
            $event->cancel();

            if (!$this->withPortal) {
                $player->sendMessage(TextFormat::colorize('&cThis duel world does not contain portals'));
                return;
            }

            if ($this->name === '') {
                return;
            }
            $world = $this->name;

            if ($position->getWorld()->getFolderName() !== $world) {
                $player->sendMessage(TextFormat::colorize('&cYou can\'t set a second portal in another world'));
                return;
            }
            $this->setSecondPortal(Position::fromObject($position->add(0, 1, 0), $position->getWorld()));
            $player->sendMessage(TextFormat::colorize('&aYou have set the second portal'));

        } elseif ($item->getId() === ItemIds::DYE && $item->getMeta() === 10) {
            $event->cancel();

            $this->create($player);
        } elseif ($item->getId() === ItemIds::DYE && $item->getMeta() === 1) {
            $event->cancel();

            $this->finalizeCreator($player);
            $player->sendMessage(TextFormat::colorize('&cDuel creator was cancelled'));
        }
    }

    public function setFirstPosition(Position $position): void {
        $this->firstPosition = $position;
    }

    public function setSecondPosition(Position $position): void {
        $this->secondPosition = $position;
    }

    public function setFirstPortal(Position $position): void {
        $this->firstPortal = $position;
    }

    public function setSecondPortal(Position $position): void {
        $this->secondPortal = $position;
    }

    private function create(Player $player): void {
        $name = $this->name;

        if ($this->name === '') {
            $player->sendMessage(TextFormat::colorize('&cWorld is null'));
            return;
        }

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

        $this->finalizeCreator($player);
        $player->sendMessage(TextFormat::colorize('&aDuel world ' . $name . ' successfully created'));
    }

    public function finalizeCreator(Player $player): void {
        /** @var Session $session */
        $session = SessionFactory::get($player);

        $player->getArmorInventory()->clearAll();
        $player->getInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();

        $player->teleport(Server::getInstance()->getWorldManager()->getDefaultWorld()?->getSpawnLocation());
        $player->setGamemode(GameMode::SURVIVAL());

        $session->giveLobyyItems();
        $session->stopSetupDuelHandler();
    }
}