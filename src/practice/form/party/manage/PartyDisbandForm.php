<?php

declare(strict_types=1);

namespace practice\form\party\manage;

use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\party\Party;

final class PartyDisbandForm extends SimpleForm {

    public function __construct(Party $party) {
        parent::__construct(TextFormat::colorize('&cParty Disband'), TextFormat::colorize('&fAre you sure you want to leave the party?' . PHP_EOL . 'This action will cause the elimination of the party'));

        $yes = new Button(TextFormat::colorize('&aYes'));
        $no = new Button(TextFormat::colorize('&cNo'));

        $this->addButton($yes, static function (Player $player, int $button_index) use ($party): void {
            $party->disband();
        });
        $this->addButton($no);
    }
}