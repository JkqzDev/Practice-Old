<?php

declare(strict_types=1);

namespace practice\item\duel;

use practice\item\PracticeItem;
use practice\Practice;
use practice\session\SessionFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class RankedItem extends PracticeItem {
    
    public function __construct() {
        parent::__construct('&bRanked Duels', ItemIds::DIAMOND_SWORD);
    }
    
    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult {
        $session = SessionFactory::get($player);
        $duelManager = Practice::getInstance()->getDuelManager();
        
        if ($session === null) {
            return ItemUseResult::FAIL();
        }
        return ItemUseResult::SUCCESS();
    }
}