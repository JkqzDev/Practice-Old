<?php

declare(strict_types=1);

namespace practice\item\party;

use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\item\ItemUseResult;
use pocketmine\utils\TextFormat;
use practice\form\party\manage\PartyDisbandForm;
use practice\item\PracticeItem;
use practice\session\SessionFactory;

final class PartyLeaveItem extends PracticeItem {

    public function __construct() {
        parent::__construct('&cLeave Party', ItemIds::DYE, 1);
    }

    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult {
        $session = SessionFactory::get($player);

        if ($session === null) {
            return ItemUseResult::FAIL();
        }
        $party = $session->getParty();

        if ($party === null) {
            return ItemUseResult::FAIL();
        }

        if (!$party->isOwner($player)) {
            $session->giveLobyyItems();
            $session->setParty(null);

            $party->broadcastMessage('&c' . $player->getName() . ' left the party');
        } else {
            $form = new PartyDisbandForm($party);
            $player->sendForm($form);
        }
        return ItemUseResult::SUCCESS();
    }
}