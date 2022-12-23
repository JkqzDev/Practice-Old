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
use practice\party\invite\Invite;
use practice\party\invite\InviteFactory;
use practice\party\Party;
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
        $this->addButton($playerInvitations, function (Player $player, int $button_index): void {
            $player->sendForm($this->createPlayerInvitationsForm($player));
        });
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
                $parties = array_filter(PartyFactory::getAll(), function (Party $party): bool {
                    return $party->isOpen();
                });

                foreach ($parties as $party) {
                    $button = new Button($party->getName());

                    $this->addButton($button, function (Player $player, int $button_index) use ($session, $party): void {
                        if ($session->inParty()) {
                            return;
                        }

                        if (PartyFactory::get($party->getName()) === null) {
                            $player->sendMessage(TextFormat::colorize('&cParty has been deleted'));
                            return;
                        }

                        if (!$party->isOpen()) {
                            $player->sendMessage(TextFormat::colorize('&cParty has been closed'));
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
                        $party->addMember($player);
                    });
                }
            }
        };
    }

    private function createPlayerInvitationsForm(Player $player): SimpleForm {
        return new class($player) extends SimpleForm {

            public function __construct(Player $player) {
                parent::__construct(TextFormat::colorize('&7Player Invitations'));

                foreach (InviteFactory::get($player) ?? [] as $invite) {
                    assert($invite instanceof Invite);
                    $button = new Button($invite->getParty()->getName());

                    $this->addButton($button, function (Player $player, int $button_index) use ($invite): void {
                        $session = SessionFactory::get($player);
                        $party = $invite->getParty();
                        if ($session === null) {
                            InviteFactory::removeFromParty($player, $party->getName());
                            return;
                        }

                        if ($session->inParty()) {
                            InviteFactory::removeFromParty($player, $party->getName());
                            return;
                        }

                        if (!$invite->exists()) {
                            InviteFactory::remove($player, $party->getName());
                            $player->sendMessage(TextFormat::colorize('&cParty has been deleted!'));
                            return;
                        }

                        if (!$invite->canJoin()) {
                            InviteFactory::remove($player, $party->getName());
                            $player->sendMessage(TextFormat::colorize('&cYou can\'t join to the party'));
                            return;
                        }
                        $party->addMember($player);
                        InviteFactory::remove($player);
                    });
                }
            }
        };
    }
}