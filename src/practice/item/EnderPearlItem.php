<?php

declare(strict_types=1);

namespace practice\item;

use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\item\EnderPearl;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\item\ItemUseResult;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\entity\EnderPearl as EntityEnderPearl;
use practice\session\SessionFactory;

class EnderPearlItem extends EnderPearl {

    public function __construct() {
        parent::__construct(new ItemIdentifier(ItemIds::ENDER_PEARL, 0), 'Ender Pearl');
    }

    public function getThrowForce(): float {
        return 2.35;
    }

    protected function createEntity(Location $location, Player $thrower): Throwable {
        return new EntityEnderPearl($location, $thrower);
    }

    public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult {
        $session = SessionFactory::get($player);

        if ($session === null) {
            return ItemUseResult::FAIL();
        }
        $countdown = $session->getEnderpearl();

        if ($countdown !== null && $countdown > microtime(true)) {
            $player->sendMessage(TextFormat::colorize('&cYou have enderpearl cooldown'));
            return ItemUseResult::FAIL();
        }
        $result = parent::onClickAir($player, $directionVector);

        if ($result->equals(ItemUseResult::SUCCESS())) {
            $session->setEnderpearl(microtime(true) + 15.0);
        }
        return $result;
    }
}