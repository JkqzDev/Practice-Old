<?php

declare(strict_types=1);

namespace practice\item\party;

use pocketmine\item\ItemIds;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use practice\form\party\PartyForm;
use practice\item\PracticeItem;
use practice\session\SessionFactory;

final class PartyItem extends PracticeItem {

    public function __construct() {
        parent::__construct('&5Party', ItemIds::CLOCK);
    }

    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult {
        $session = SessionFactory::get($player);

        if ($session === null) {
            return ItemUseResult::FAIL();
        }

        if (!$session->inLobby() || $session->inParty()) {
            return ItemUseResult::FAIL();
        }
        $form = new PartyForm($session);
        $player->sendForm($form);
        return ItemUseResult::SUCCESS();
    }
}