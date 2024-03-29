<?php

declare(strict_types=1);

namespace practice\item\party;

use pocketmine\item\ItemIds;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use practice\form\party\manage\PartyDuelInviteForm;
use practice\item\PracticeItem;
use practice\session\SessionFactory;

final class PartyDuelInviteItem extends PracticeItem {

    public function __construct() {
        parent::__construct('&eDuel Invite', ItemIds::GOLDEN_SWORD);
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
        $form = new PartyDuelInviteForm($party);
        $player->sendForm($form);
        return ItemUseResult::SUCCESS();
    }
}