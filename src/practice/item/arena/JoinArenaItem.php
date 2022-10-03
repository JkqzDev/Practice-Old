<?php

declare(strict_types=1);

namespace practice\item\arena;

use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\item\ItemUseResult;
use practice\form\arena\ArenaForm;
use practice\item\PracticeItem;
use practice\session\SessionFactory;

class JoinArenaItem extends PracticeItem {

    public function __construct() {
        parent::__construct('&eArena FFA', ItemIds::GOLD_SWORD);
    }

    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult {
        $session = SessionFactory::get($player);

        if ($session === null) {
            return ItemUseResult::FAIL();
        }

        if (!$session->inLobby()) {
            return ItemUseResult::FAIL();
        }
        $form = new ArenaForm;
        $player->sendForm($form);
        return ItemUseResult::SUCCESS();
    }
}