<?php

declare(strict_types=1);

namespace practice\item\party;

use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\item\ItemUseResult;
use practice\form\party\manage\PartyInformationForm;
use practice\item\PracticeItem;
use practice\session\SessionFactory;

final class PartyInformationItem extends PracticeItem {

    public function __construct() {
        parent::__construct('&8Party Information', ItemIds::PAPER);
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
        $form = new PartyInformationForm($party);
        $player->sendForm($form);
        return ItemUseResult::SUCCESS();
    }
}