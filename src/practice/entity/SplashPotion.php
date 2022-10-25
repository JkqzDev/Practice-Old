<?php

// Splash Potion by Wqrro
// Thanks Wqrro

declare(strict_types=1);

namespace practice\entity;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\color\Color;
use pocketmine\entity\effect\InstantEffect;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\SplashPotion as ProjectileSplashPotion;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\item\PotionType;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\world\particle\PotionSplashParticle;
use pocketmine\world\sound\PotionSplashSound;

class SplashPotion extends ProjectileSplashPotion {

    public const MAX_HIT = 1.0515;
	public const MAX_MISS = 0.9215;

	protected $gravity = 0.06;
	protected $drag = 0.0025;

    public function __construct(Location $location, ?Entity $shootingEntity, PotionType $potionType, ?CompoundTag $nbt = null) {
        parent::__construct($location, $shootingEntity, $potionType, $nbt);
        $this->setScale(0.6);
    }

    protected function onHit(ProjectileHitEvent $event): void {
        $effects = $this->getPotionEffects();
		$hasEffects = true;

		if (count($effects) === 0) {
			$particle = new PotionSplashParticle(PotionSplashParticle::DEFAULT_COLOR());
			$hasEffects = false;
		} else {
			$colors = [];
			foreach($effects as $effect){
				$level = $effect->getEffectLevel();
				for($j = 0; $j < $level; ++$j){
					$colors[] = $effect->getColor();
				}
			}
			$particle = new PotionSplashParticle(Color::mix(...$colors));
		}

		$this->getWorld()->addParticle($this->getLocation(), $particle);
		$this->broadcastSound(new PotionSplashSound);

        if ($hasEffects) {
            if ($event instanceof ProjectileHitEntityEvent) {
                $entityHit = $event->getEntityHit();

                if ($entityHit instanceof Player) {
                    $entityHit->heal(new EntityRegainHealthEvent($entityHit, 1.45, EntityRegainHealthEvent::CAUSE_CUSTOM));
                }
            }

            foreach ($this->getWorld()->getNearbyEntities($this->getBoundingBox()->expand(1.85, 2.65, 1.85)) as $entity) {
                if ($entity instanceof Player && $entity->isAlive()) {
                    foreach ($effects as $effect) {
                        if (!$effect->getType() instanceof InstantEffect) {
                            $newDuration = (int) round($effect->getDuration() * 0.75 * self::MAX_HIT);

                            if ($newDuration < 20) {
                                continue;
                            }
                            $effect->setDuration($newDuration);
                            $entity->getEffects()->add($effect);
                        } else {
                            $effect->getType()->applyEffect($entity, $effect, self::MAX_HIT, $this);
                        }
                    }
                }
            }
        }
    }

    public function calculateInterceptWithBlock(Block $block, Vector3 $start, Vector3 $end): ?RayTraceResult {
		if ($block->getId() === BlockLegacyIds::INVISIBLE_BEDROCK) {
			return null;
		}
		return parent::calculateInterceptWithBlock($block, $start, $end);
	}

    public function entityBaseTick(int $tickDiff = 1): bool {
        if ($this->isCollided) {
            $this->flagForDespawn();
        }
        return parent::entityBaseTick($tickDiff);
    }
}