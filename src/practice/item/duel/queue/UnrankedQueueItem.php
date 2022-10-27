<?php

declare(strict_types=1);

namespace practice\item\duel\queue;

use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use practice\item\PracticeItem;
use pocketmine\item\ItemUseResult;
use practice\session\SessionFactory;
use practice\form\duel\DuelQueueForm;

class UnrankedQueueItem extends PracticeItem {

    public function __construct() {
        parent::__construct('&9Unranked Duels', ItemIds::IRON_SWORD);
    }

    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult {
        $session = SessionFactory::get($player);

        if ($session === null || !$session->inLobby()) {
            return ItemUseResult::FAIL();
        }
        $form = new DuelQueueForm;
        $player->sendForm($form);
        return ItemUseResult::SUCCESS();
    }
}