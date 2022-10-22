<?php

declare(strict_types=1);

namespace practice\item\duel;

use pocketmine\item\ItemIds;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use practice\form\duel\DuelSpectateForm;
use practice\item\PracticeItem;
use practice\session\SessionFactory;

class DuelSpectateItem extends PracticeItem {

    public function __construct() {
        parent::__construct('&3Spectate', ItemIds::COMPASS);
    }

    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult {
        $session = SessionFactory::get($player);
        
        if ($session === null) {
            return ItemUseResult::FAIL();
        }
        
        if (!$session->inLobby()) {
            return ItemUseResult::FAIL();
        }
        $form = new DuelSpectateForm;
        $player->sendForm($form);
        return ItemUseResult::SUCCESS();
    }
}