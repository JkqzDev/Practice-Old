<?php

declare(strict_types=1);

namespace practice\duel\type;

use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\item\ItemIds;
use practice\duel\Duel;

class Nodebuff extends Duel {

    public function handleItemUse(PlayerItemUseEvent $event): void {
        $item = $event->getItem();

        if (!$this->isRunning() && $item->getId() === ItemIds::ENDER_PEARL) {
            $event->cancel();
        }
    }
}