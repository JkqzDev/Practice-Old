<?php

declare(strict_types=1);

namespace practice\item\duel;

use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\item\ItemUseResult;
use practice\duel\queue\QueueFactory;
use practice\item\PracticeItem;
use practice\Practice;

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
            $session->setQueue(null);
            
            QueueFactory::remove($player);
        }
        return ItemUseResult::SUCCESS();
    }
}