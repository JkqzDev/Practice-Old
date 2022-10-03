<?php

declare(strict_types=1);

namespace practice\form\arena;

use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\Practice;
use practice\session\SessionFactory;

class ArenaForm extends SimpleForm {

    public function __construct() {
        $plugin = Practice::getInstance();
        $manager = $plugin->getArenaManager();

        parent::__construct(TextFormat::colorize('&eArenas FFA'));

        foreach ($manager->getArenas() as $arena) {
            $this->addButton(new Button(TextFormat::colorize('&7' . $arena->getName() . PHP_EOL . '&fPlaying: ' . count($arena->getPlayers()))), function (Player $player, int $button_index) use ($arena): void {
                $session = SessionFactory::get($player);

                if ($session === null) {
                    return;
                }

                if (!$session->inLobby()) {
                    return;
                }
                $arena->join($player);
            });
        }
    }
}