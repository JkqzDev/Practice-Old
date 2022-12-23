<?php

declare(strict_types=1);

namespace practice\form\arena;

use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\arena\ArenaFactory;
use practice\Practice;
use practice\session\SessionFactory;

final class ArenaForm extends SimpleForm {

    public function __construct() {
        $plugin = Practice::getInstance();
        parent::__construct(TextFormat::colorize('&eArenas FFA'));

        foreach (ArenaFactory::getAll() as $arena) {
            $this->addButton(new Button(TextFormat::colorize('&7' . $arena->getName() . PHP_EOL . '&fPlaying: ' . count($arena->getPlayers()))),
                static function (Player $player, int $button_index) use ($arena): void {
                    $session = SessionFactory::get($player);

                    if ($session === null || !$session->inLobby()) {
                        return;
                    }
                    $session->setArena($arena);
                    $arena->join($player);
                }
            );
        }
    }
}