<?php

declare(strict_types=1);

namespace practice\item\party;

use pocketmine\item\ItemIds;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use practice\form\party\manage\PartyDuelForm;
use practice\item\PracticeItem;
use practice\session\SessionFactory;

final class PartyDuelItem extends PracticeItem {

    public function __construct() {
        parent::__construct('&bParty Duel', ItemIds::DIAMOND_SWORD);
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

        /*if (count($party->getMembers()) < 2) {
            $player->sendMessage(TextFormat::colorize('&cYou cannot duel with less than two players.'));
            return ItemUseResult::FAIL();
        }*/
        $form = new PartyDuelForm;
        $player->sendForm($form);
        return ItemUseResult::SUCCESS();
    }
}