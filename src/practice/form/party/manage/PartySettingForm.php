<?php

declare(strict_types=1);

namespace practice\form\party\manage;

use cosmicpe\form\CustomForm;
use cosmicpe\form\entries\custom\StepSliderEntry;
use cosmicpe\form\entries\custom\ToggleEntry;
use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\party\Party;
use practice\session\SessionFactory;

final class PartySettingForm extends SimpleForm {

    public function __construct(Party $party) {
        parent::__construct(TextFormat::colorize('&gParty Settings'));
        $manageMembers = new Button(TextFormat::colorize('&7Manage Members'));
        $manageParty = new Button(TextFormat::colorize('&7Manage Party'));
        $invitePlayer = new Button(TextFormat::colorize('&7Invite Player'));

        $this->addButton($manageMembers, function (Player $player, int $button_index) use ($party): void {
            $player->sendForm($this->formManageMembers($party));
        });
        $this->addButton($manageParty, function (Player $player, int $button_index) use ($party): void {
            $player->sendForm($this->formManageParty($party));
        });
        $this->addButton($invitePlayer, function (Player $player, int $button_index): void {

        });
    }

    private function formManageParty(Party $party): CustomForm {
        return new class($party) extends CustomForm {

            public function __construct(Party $party) {
                parent::__construct(TextFormat::colorize('&7Manage Party'));
                $players = [(string) Party::DEFAULT_PLAYERS, (string) Party::EIGHT_PLAYERS, (string) Party::TEN_PLAYERS];

                $maxPlayers = new StepSliderEntry('Max Players', $players);
                $maxPlayers->setDefault((string) $party->getMaxPlayers());
                $isOpen = new ToggleEntry('Open Party', $party->isOpen());

                $this->addEntry($isOpen, function (Player $player, ToggleEntry $entry, bool $value) use ($party): void {
                    if ($party->isOpen() === $value) {
                        return;
                    }
                    $party->setOpen($value);
                });

                $this->addEntry($maxPlayers, function (Player $player, StepSliderEntry $entry, int $value) use ($party, $players): void {
                    if ($value === 0) {
                        $party->setMaxPlayers(Party::DEFAULT_PLAYERS);
                    } else {
                        $maxPlayers = (int) $players[$value];
                        $party->setMaxPlayers($maxPlayers);
                    }
                    $player->sendMessage(TextFormat::colorize('&aYou have successfully edited the party settings'));
                });
            }
        };
    }

    private function formManageMembers(Party $party): SimpleForm {
        return new class($party, $this) extends SimpleForm {

            public function __construct(Party $party, PartySettingForm $form) {
                parent::__construct(TextFormat::colorize('&7Manage Members'));

                foreach ($party->getMembers() as $member) {
                    $this->addButton(new Button(TextFormat::colorize('&7' . $member->getName() . ($party->isOwner($member) ? PHP_EOL . '&cOWNER' : ''))), function (Player $player, int $button_index) use ($party, $form, $member): void {
                        if (!$member->isOnline() || $member->getId() === $player->getId()) {
                            return;
                        }
                        $session = SessionFactory::get($member);

                        if ($session === null) {
                            return;
                        }

                        if ($session->getParty() === null || $session->getParty()->getName() !== $party->getName()) {
                            $player->sendMessage(TextFormat::colorize('&cThis player left the party'));
                            return;
                        }
                        $player->sendForm($form->formManageMember($party, $member));
                    });
                }
            }
        };
    }

    public function formManageMember(Party $party, Player $member): SimpleForm {
        return new class($party, $member) extends SimpleForm {
            
            public function __construct(Party $party, Player $member) {
                parent::__construct(TextFormat::colorize('&7' . $member->getName() . ' Manage'));
                $promote = new Button(TextFormat::colorize('&aPromote to Owner'));
                $kick = new Button(TextFormat::colorize('&cKick from the party'));

                $this->addButton($promote, function (Player $player, int $button_index) use ($party, $member): void {
                    if (!$member->isOnline()) {
                        $player->sendMessage(TextFormat::colorize('&cThis player left the party'));
                        return;
                    }
                    $session = SessionFactory::get($member);

                    if ($session === null) {
                        return;
                    }

                    if ($session->getParty() === null || $session->getParty()->getName() !== $party->getName()) {
                        $player->sendMessage(TextFormat::colorize('&cThis player left the party'));
                        return;
                    }
                    $party->setOwner($member);

                    $member->sendMessage(TextFormat::colorize('&aNow you are the owner of the party'));
                    $player->sendMessage(TextFormat::colorize('&aYou have promoted ' . $member->getName() . ' to owner'));

                    $party->giveItems($member);
                    $party->giveItems($player);
                });

                $this->addButton($kick, function (Player $player, int $button_index) use ($party, $member): void {
                    if (!$member->isOnline()) {
                        $player->sendMessage(TextFormat::colorize('&cThis player left the party'));
                        return;
                    }
                    $session = SessionFactory::get($member);

                    if ($session === null) {
                        return;
                    }

                    if ($session->getParty() === null || $session->getParty()->getName() !== $party->getName()) {
                        $player->sendMessage(TextFormat::colorize('&cThis player left the party'));
                        return;
                    }
                    $party->removeMember($member);
                    $party->broadcastMessage('&c' . $member->getName(). ' has been kicked!');

                    $member->getArmorInventory()->clearAll();
                    $member->getInventory()->clearAll();
                    $member->getOffHandInventory()->clearAll();
                    $member->getCursorInventory()->clearAll();
                    $member->sendMessage(TextFormat::colorize('&cYou has been kicked of the party'));

                    $session->setParty(null);
                    $session->giveLobyyItems();

                    $player->sendMessage(TextFormat::colorize('&cYou have kicked ' . $member->getName()));
                });
            }
        };
    }
}