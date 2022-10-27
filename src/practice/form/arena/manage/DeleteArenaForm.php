<?php

declare(strict_types=1);

namespace practice\form\arena\manage;

use practice\arena\Arena;
use cosmicpe\form\CustomForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\arena\ArenaFactory;
use cosmicpe\form\entries\custom\InputEntry;

final class DeleteArenaForm extends CustomForm {

    public function __construct() {
        parent::__construct(TextFormat::colorize('&bDelete Arena'));

        $nameEntry = new InputEntry('Arena name', 'Nodebuff');

        $this->addEntry($nameEntry, static function(Player $player, InputEntry $entry, string $value): void {
            if (ArenaFactory::get($value) === null) {
                $player->sendMessage(TextFormat::colorize('&cArena not exists!'));
                return;
            }
            /** @var Arena $arena */
            $arena = ArenaFactory::get($value);

            foreach ($arena->getPlayers() as $target) {
                $arena->quit($target, false);
            }
            ArenaFactory::remove($value);
            $player->sendMessage(TextFormat::colorize('&cYou have successfully removed the arena'));
        });
    }
}