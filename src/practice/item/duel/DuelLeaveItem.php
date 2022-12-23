<?php

declare(strict_types=1);

namespace practice\item\duel;

use pocketmine\item\ItemIds;
use practice\item\PracticeItem;

final class DuelLeaveItem extends PracticeItem {

    public function __construct() {
        parent::__construct('&cLeave', ItemIds::BED);
    }
}