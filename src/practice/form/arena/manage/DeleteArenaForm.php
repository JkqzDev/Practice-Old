<?php

declare(strict_types=1);

namespace practice\form\arena\manage;

use cosmicpe\form\CustomForm;
use cosmicpe\form\entries\custom\InputEntry;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\arena\Arena;
use practice\arena\ArenaFactory;

final class DeleteArenaForm extends CustomForm {

    public function __construct() {
        parent::__construct(TextFormat::colorize('&cDelete Arena'));

        $nameEntry = new InputEntry('Arena name', 'Nodebuff');

        $this->addEntry($nameEntry, static function (Player $player, InputEntry $entry, string $value): void {
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