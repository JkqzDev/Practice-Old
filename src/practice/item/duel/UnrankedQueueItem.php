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
use practice\form\duel\DuelQueueForm;

class UnrankedQueueItem extends PracticeItem {
    
    public function __construct() {
        parent::__construct('&9Unranked Duels', ItemIds::IRON_SWORD);
    }
    
    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult {
        $session = SessionFactory::get($player);
        $duelManager = Practice::getInstance()->getDuelManager();
        
        if ($session === null) {
            return ItemUseResult::FAIL();
        }
        
        if (!$session->inLobby()) {
            return ItemUseResult::FAIL();
        }
        $form = new DuelQueueForm();
        $player->sendForm($form);
        return ItemUseResult::SUCCESS();
    }
}