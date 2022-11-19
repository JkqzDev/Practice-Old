<?php

declare(strict_types=1);

namespace practice\form\party\manage;

use cosmicpe\form\CustomForm;
use cosmicpe\form\entries\custom\DropdownEntry;
use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\party\duel\Duel;
use practice\party\duel\DuelFactory;
use practice\party\duel\invite\Invite;
use practice\party\duel\invite\InviteFactory;
use practice\party\Party;
use practice\party\PartyFactory;

final class PartyDuelInviteForm extends SimpleForm {

    public function __construct(Party $party) {
        parent::__construct(TextFormat::colorize('&eDuel Invite'));
        $inviteParty = new Button(TextFormat::colorize('&7Invite Party'));
        $partyRequests = new Button(TextFormat::colorize('&7Party Requests'));

        $this->addButton($inviteParty, function(Player $player, int $button_index) use ($party): void {
            $player->sendForm($this->createPartyInviteForm($party));
        });

        $this->addButton($partyRequests, function(Player $player, int $button_index) use ($party): void {
            $player->sendForm($this->createPartyRequestsForm($party));
        });
    }

    private function createPartyInviteForm(Party $party): CustomForm {
        return new class($party) extends CustomForm {

            private array $types = [
                'No Debuff' => Duel::TYPE_NODEBUFF,
                'Gapple' => Duel::TYPE_GAPPLE
            ];

            public function __construct(
                Party $party,
                private ?Party $target = null
            ) {
                parent::__construct(TextFormat::colorize('&7Party Duel Invite'));
                $duels = array_keys($this->types);
                $parties = array_keys(array_filter(PartyFactory::getAll(), function (Party $target) use ($party): bool {
                    return $party->getName() !== $target->getName() && !$target->inDuel();
                }));

                $partiesDropdown = new DropdownEntry('Choose Party', $parties);
                $duelsDropdown = new DropdownEntry('Choose Duel', $duels);

                $this->addEntry($partiesDropdown, function(Player $player, DropdownEntry $entry, int $value) use ($parties): void {
                    $targetName = $parties[$value];
                    $target = PartyFactory::get($targetName);

                    if ($target === null) {
                        $player->sendMessage(TextFormat::colorize('&cParty has been disbaned!'));
                        return;
                    }

                    if ($target->inDuel()) {
                        $player->sendMessage(TextFormat::colorize('&cParty has already duel'));
                        return;
                    }
                    $this->target = $target;
                });

                $this->addEntry($duelsDropdown, function(Player $player, DropdownEntry $entry, int $value) use ($party): void {
                    if ($this->target === null) {
                        return;
                    }
                    $duels = array_keys($this->types);
                    $duelName = $duels[$value];

                    InviteFactory::create($this->target, $party, $value);
                    $player->sendMessage(TextFormat::colorize('&aYou have sent a party duel invite to ' . $this->target->getName() . ' in ' . $duelName));
                    $this->target->getOwner()->sendMessage(TextFormat::colorize('&aYou have received a ' . $duelName . ' party duel invite from ' . $party->getName() . '. See your requests through the item in your inventory'));
                });
            }
        };
    }

    private function createPartyRequestsForm(Party $party): SimpleForm {
        return new class($party) extends SimpleForm {

            public function __construct(Party $party) {
                parent::__construct(TextFormat::colorize('&7Party Duel Requests'));
                
                foreach (InviteFactory::get($party) ?? [] as $invite) {
                    assert($invite instanceof Invite);

                    if ($invite->isExpired()) {
                        continue;
                    }
                    $button = new Button($invite->getParty()->getName());

                    $this->addButton($button, function(Player $player, int $button_index) use ($party, $invite): void {
                        $player->sendForm($this->createRequestReponseForm($party, $invite));
                    });
                }
            }

            private function createRequestReponseForm(Party $party, Invite $invite): SimpleForm {
                return new class($party, $invite) extends SimpleForm {
                    
                    public function __construct(Party $party, Invite $invite) {
                        parent::__construct(TextFormat::colorize('&7' . $invite->getParty()->getName(). '\'s invite'));
                        $accept = new Button(TextFormat::colorize('&aAccept'));
                        $decline = new Button(TextFormat::colorize('&cDecline'));
        
                        $this->addButton($accept, function(Player $player, int $button_index) use ($party, $invite): void {
                            $target = $invite->getParty();
        
                            if (!$invite->exists()) {
                                InviteFactory::removeFromParty($party, $target);
                                return;
                            }
        
                            if ($invite->isExpired()) {
                                InviteFactory::removeFromParty($party, $target);
                                $player->sendMessage(TextFormat::colorize('&cInvite already expired'));
                                return;
                            }
        
                            if ($target->inDuel()) {
                                InviteFactory::removeFromParty($party, $target);
                                $player->sendMessage(TextFormat::colorize('&cThe party has already in duel'));
                                return;
                            }
        
                            if ($party->inDuel()) {
                                InviteFactory::removeFromParty($party, $target);
                                return;
                            }
                            $party->setQueue(null);
                            $target->setQueue(null);
        
                            DuelFactory::create($party, $target, $invite->getDuelType());
        
                            $target->getOwner()->sendMessage(TextFormat::colorize('&a' . $party->getName() . ' accepted your party duel request'));
                            $player->sendMessage(TextFormat::colorize('&aYou have accepted ' . $target->getName() . '\'s request'));
        
                            InviteFactory::remove($party);
                            InviteFactory::remove($target);
                        });
        
                        $this->addButton($decline, function(Player $player, int $button_index) use ($party, $invite): void {
                            $target = $invite->getParty();
        
                            if ($invite->exists() && !$target->inDuel()) {
                                $target->getOwner()->sendMessage(TextFormat::colorize('&c' . $party->getName() . ' declines your party duel request'));
                            }
                            $player->sendMessage(TextFormat::colorize('&cYou have decline ' . $target->getName() . '\'s request'));
                            InviteFactory::removeFromParty($party, $target);
                        });
                    }
                };
            }
        };
    }
}