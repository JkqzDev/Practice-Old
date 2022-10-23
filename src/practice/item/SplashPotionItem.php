<?php

declare(strict_types=1);

namespace practice\item;

use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\ItemUseResult;
use pocketmine\item\PotionType;
use pocketmine\item\SplashPotion;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use practice\entity\SplashPotion as EntitySplashPotion;

class SplashPotionItem extends SplashPotion {

    public function __construct(
        private PotionType $type
    ) {
        parent::__construct(new ItemIdentifier(ItemIds::SPLASH_POTION, PotionTypeIdMap::getInstance()->toId($type)), $type->getDisplayName(), $type);
    }

    public function getThrowForce(): float {
		return 0.5;
	}

    protected function createEntity(Location $location, Player $thrower): Throwable {
		return new EntitySplashPotion($location, $thrower, $this->type);
	}

	public function onClickAir(Player $player, Vector3 $directionVector): ItemUseResult {
		return parent::onClickAir($player, $directionVector);
	}
}