<?php

declare(strict_types=1);

namespace practice\form\party;

use cosmicpe\form\CustomForm;
use cosmicpe\form\entries\custom\InputEntry;
use cosmicpe\form\entries\custom\ToggleEntry;
use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\party\PartyFactory;
use practice\session\Session;
use practice\session\SessionFactory;

final class PartyForm extends SimpleForm {

    public function __construct(Session $session) {
        parent::__construct(TextFormat::colorize('&5Party Menu'));
        $createParty = new Button(TextFormat::colorize('&7Create Party'));
        $publicParties = new Button(TextFormat::colorize('&7Public Parties'));
        $playerInvitations = new Button(TextFormat::colorize('&7Your invitations'));

        $this->addButton($createParty, function (Player $player, int $button_index) use ($session): void {
            $player->sendForm($this->formCreateParty($session));
        });
        $this->addButton($publicParties, function (Player $player, int $button_index) use ($session): void {
            $player->sendForm($this->formListParty($session));
        });
        $this->addButton($playerInvitations, function (Player $player, int $button_index) use ($session): void {});
    }

    private function formCreateParty(Session $session): CustomForm {
        return new class($session) extends CustomForm {

            public function __construct(Session $session) {
                parent::__construct(TextFormat::colorize('&7Create Party'));
                $defaultName = $session->getName() . '\'s party';
                
                $nameParty = new InputEntry('Party Name', null, $defaultName);
                $isOpen = new ToggleEntry('Party Open', true);

                $this->addEntry($nameParty, function (Player $player, InputEntry $entry, string $value) use ($session, &$defaultName): void {
                    $defaultName = $value;
                });

                $this->addEntry($isOpen, function (Player $player, ToggleEntry $entry, bool $value) use ($session, &$defaultName): void {
                    if (PartyFactory::get($defaultName) !== null) {
                        $player->sendMessage(TextFormat::colorize('&cThe party you are trying to create already exists'));
                        return;
                    }
                    PartyFactory::create($session, $defaultName, $value);
                });
            }
        };
    }

    private function formListParty(Session $session): SimpleForm {
        return new class($session) extends SimpleForm {

            public function __construct(Session $session) {
                parent::__construct(TextFormat::colorize('&7Parties Open'));
                
                foreach (PartyFactory::getAll() as $party) {
                    $button = new Button($party->getName());

                    $this->addButton($button, function (Player $player, int $button_index) use ($session, $party): void {
                        if ($session->getParty() !== null) {
                            return;
                        }

                        if (PartyFactory::get($party->getName()) === null) {
                            $player->sendMessage(TextFormat::colorize('&cParty has been deleted'));
                            return;
                        }

                        if ($party->isFull()) {
                            $player->sendMessage(TextFormat::colorize('&cParty is full!'));
                            return;
                        }

                        if ($party->inDuel()) {
                            $player->sendMessage(TextFormat::colorize('&cParty is in duel!'));
                            return;
                        }
                        $party->addMemeber($player);
                    });
                }
            }
        };
    }
}