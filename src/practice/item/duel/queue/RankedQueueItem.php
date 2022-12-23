<?php

declare(strict_types=1);

namespace practice\item\duel\queue;

use pocketmine\item\ItemIds;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use practice\form\duel\DuelQueueForm;
use practice\item\PracticeItem;
use practice\session\SessionFactory;

class RankedQueueItem extends PracticeItem {

    public function __construct() {
        parent::__construct('&bRanked Duels', ItemIds::DIAMOND_SWORD);
    }

    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult {
        $session = SessionFactory::get($player);

        if ($session === null || !$session->inLobby()) {
            return ItemUseResult::FAIL();
        }
        $form = new DuelQueueForm(true);
        $player->sendForm($form);
        return ItemUseResult::SUCCESS();
    }
}