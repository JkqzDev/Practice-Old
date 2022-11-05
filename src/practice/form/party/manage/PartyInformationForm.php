<?php

declare(strict_types=1);

namespace practice\form\party\manage;

use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use pocketmine\utils\TextFormat;
use practice\party\Party;

final class PartyInformationForm extends SimpleForm {

    public function __construct(Party $party) {
        parent::__construct(TextFormat::colorize('&9Party Information'));

        foreach ($party->getMembers() as $member) {
            $this->addButton(new Button(TextFormat::colorize('&7' . $member->getName() . ($party->isOwner($member) ? PHP_EOL . '&cOWNER' : ''))));
        }
    }
}