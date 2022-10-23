<?php

declare(strict_types=1);

namespace practice\form\duel\manage;

use cosmicpe\form\CustomForm;
use cosmicpe\form\entries\custom\InputEntry;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\world\WorldFactory;

final class DeleteDuelForm extends CustomForm {

    public function __construct() {
        parent::__construct(TextFormat::colorize('&bDelete Duel World'));

        $nameEntry = new InputEntry('World name', 'world');

        $this->addEntry($nameEntry, function (Player $player, InputEntry $entry, string $value): void {
            if (WorldFactory::get($value) === null) {
                $player->sendMessage(TextFormat::colorize('&cWorld duel not exists!'));
                return;
            }
            WorldFactory::remove($value);
            $player->sendMessage(TextFormat::colorize('&cYou have successfully removed the world duel'));
        });
    }
}