<?php

declare(strict_types=1);

namespace practice\form\duel\manage;

use pocketmine\Server;
use pocketmine\player\Player;
use cosmicpe\form\CustomForm;
use pocketmine\utils\TextFormat;
use practice\world\WorldFactory;
use practice\session\SessionFactory;
use cosmicpe\form\entries\custom\InputEntry;
use practice\session\handler\SetupDuelHandler;

final class SetupDuelForm extends CustomForm {

    public function __construct(
        private ?string $name = null,
        private array   $duels = [
            'nodebuff',
            'battlerush',
            'bridge',
            'boxing',
            'sumo',
            'builduhc',
            'combo',
            'gapple',
            'fist',
            'caveuhc',
            'finaluhc'
        ]
    ) {
        parent::__construct(TextFormat::colorize('&cDuel World Setup'));
        $nameEntry = new InputEntry('World name', 'world');
        $modesEntry = new InputEntry('Duel modes', 'Nodebuff, Bridge');

        $this->addEntry($nameEntry, function(Player $player, InputEntry $entry, string $value): void {
            if (WorldFactory::get($value) !== null) {
                $player->sendMessage(TextFormat::colorize('&cWorld duel already exists'));
                return;
            }

            if (!Server::getInstance()->getWorldManager()->isWorldGenerated($value)) {
                $player->sendMessage(TextFormat::colorize('&cWorld not exists!'));
                return;
            }

            if (!Server::getInstance()->getWorldManager()->isWorldLoaded($value)) {
                Server::getInstance()->getWorldManager()->loadWorld($value, true);
            }
            $this->name = $value;
        });

        $this->addEntry($modesEntry, function(Player $player, InputEntry $entry, string $value): void {
            if ($this->name === null) {
                return;
            }
            $session = SessionFactory::get($player);

            if ($session === null) {
                return;
            }
            $withPortal = false;

            $modes = [];
            $array = explode(', ', $value);

            foreach ($array as $name) {
                if (in_array(strtolower($name), $this->duels, true)) {
                    $modes[] = strtolower($name);

                    if (strtolower($name) === 'bridge' || strtolower($name) === 'battlerush') {
                        $withPortal = true;
                    }
                }
            }

            if (count($modes) === 0) {
                $player->sendMessage(TextFormat::colorize('&cYou have not selected modes'));
                return;
            }
            $session->startSetupDuelHandler();

            /** @var SetupDuelHandler $setupDuel */
            $setupDuel = $session->getSetupDuelHandler();
            $setupDuel->setName($this->name);
            $setupDuel->setModes($modes);
            $setupDuel->setWithPortal($withPortal);

            $setupDuel->prepareCreator($player);
        });
    }
}