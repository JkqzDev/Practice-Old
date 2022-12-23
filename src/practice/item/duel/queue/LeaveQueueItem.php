<?php

declare(strict_types=1);

namespace practice\item\duel\queue;

use pocketmine\item\ItemIds;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use practice\duel\queue\QueueFactory;
use practice\item\PracticeItem;
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
        $session->giveLobbyItems();
        $session->setQueue(null);

        QueueFactory::remove($player);
        return ItemUseResult::SUCCESS();
    }
}