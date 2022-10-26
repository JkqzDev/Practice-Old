<?php

declare(strict_types=1);

namespace practice\item;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\GoldenApple;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;

class GoldenHeadItem extends GoldenApple {

    public function __construct() {
        parent::__construct(new ItemIdentifier(ItemIds::GOLDEN_APPLE, 10), 'Golden Head');
    }
    
    public function getAdditionalEffects(): array {
        return [
            new EffectInstance(VanillaEffects::REGENERATION(), 20 * 9, 1),
            new EffectInstance(VanillaEffects::ABSORPTION(), 2400)
        ];
    }
	
    public function getVanillaName(): string {
        return 'Golden Head';
    }
}