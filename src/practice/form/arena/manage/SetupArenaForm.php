<?php

declare(strict_types=1);

namespace practice\form\arena\manage;

use cosmicpe\form\CustomForm;
use cosmicpe\form\entries\custom\InputEntry;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use practice\arena\ArenaFactory;
use practice\session\handler\SetupArenaHandler;
use practice\session\SessionFactory;

final class SetupArenaForm extends CustomForm {

    public function __construct(
        private ?string $name = null,
        private ?string $kit = null
    ) {
        parent::__construct(TextFormat::colorize('&cArena Setup'));
        $nameEntry = new InputEntry('Arena name', 'No Debuff');
        $kitEntry = new InputEntry('Kit name', 'nodebuff');
        $worldEntry = new InputEntry('World name', 'world');

        $this->addEntry($nameEntry, function (Player $player, InputEntry $entry, string $value): void {
            if (ArenaFactory::get($value) !== null) {
                $player->sendMessage(TextFormat::colorize('&cArena already exists!'));
                return;
            }
            $this->name = $value;
        });

        $this->addEntry($kitEntry, function (Player $player, InputEntry $entry, string $value): void {
            $this->kit = $value;
        });

        $this->addEntry($worldEntry, function (Player $player, InputEntry $entry, string $value): void {
            if ($this->name === null || $this->kit === null) {
                return;
            }

            if (!Server::getInstance()->getWorldManager()->isWorldGenerated($value)) {
                $player->sendMessage(TextFormat::colorize('&cWorld not exists!'));
                return;
            }

            if (!Server::getInstance()->getWorldManager()->isWorldLoaded($value)) {
                Server::getInstance()->getWorldManager()->loadWorld($value, true);
            }
            $session = SessionFactory::get($player);

            if ($session === null) {
                return;
            }
            $session->startSetupArenaHandler();

            /** @var SetupArenaHandler $setupArena */
            $setupArena = $session->getSetupArenaHandler();
            $setupArena->setWorld($value);
            $setupArena->setKit($this->kit);
            $setupArena->setName($this->name);

            $setupArena->prepareCreator($player);
        });
    }
}