<?php

declare(strict_types=1);

namespace practice\item\duel;

use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\item\ItemUseResult;
use practice\item\PracticeItem;
use practice\Practice;
use practice\session\SessionFactory;

class LeaveQueueItem extends PracticeItem {

    public function __construct() {
        parent::__construct('&cLeave queue', ItemIds::REDSTONE);
    }

    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult {
        $session = SessionFactory::get($player);

        if ($session === null) {
            return ItemUseResult::FAIL();
        }
        $session->giveLobyyItems();

        if ($session->inQueue()) {
            Practice::getInstance()->getDuelManager()->removeQueue($player);
        }
        return ItemUseResult::SUCCESS();
    }
}