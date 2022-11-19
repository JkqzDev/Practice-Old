<?php

declare(strict_types=1);

namespace practice\form\party\manage;

use cosmicpe\form\CustomForm;
use cosmicpe\form\entries\custom\InputEntry;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\party\invite\InviteFactory;
use practice\party\Party;
use practice\session\SessionFactory;

final class PartyInviteForm extends CustomForm {

    public function __construct(Party $party) {
        parent::__construct(TextFormat::colorize('&dInvite Player'));
        $playerName = new InputEntry(TextFormat::colorize('&7Player Name'));

        $this->addEntry($playerName, function(Player $player, InputEntry $entry, string $value) use ($party): void {
            $target = $player->getServer()->getPlayerByPrefix($value);

            if ($target === null) {
                $player->sendMessage(TextFormat::colorize('&cPlayer is offline.'));
                return;
            }
            $session = SessionFactory::get($target);

            if ($session === null) {
                $player->sendMessage(TextFormat::colorize('&cPlayer not found.'));
                return;
            }

            if ($session->inParty()) {
                $player->sendMessage(TextFormat::colorize('&cThe player has already party'));
                return;
            }

            if (!$party->isOwner($player)) {
                return;
            }

            if ($party->isFull()) {
                $player->sendMessage(TextFormat::colorize('&cThe party is already full'));
                return;
            }
            InviteFactory::create($target, $party);

            $target->sendMessage(TextFormat::colorize('&aYou have been invited to join a party'));
            $player->sendMessage(TextFormat::colorize('&aYou have invited ' . $target->getName() . ' to join the party'));
        });
    }
}