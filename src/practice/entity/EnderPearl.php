<?php

// Ender pearl by Wqrro
// Thanks Wqrro

declare(strict_types=1);

namespace practice\entity;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\EnderPearl as ProjectileEnderPearl;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\player\Player;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\sound\EndermanTeleportSound;

class EnderPearl extends ProjectileEnderPearl {

    protected $gravity = 0.064;
    protected $drag = 0.0085;

    public function __construct(Location $location, ?Entity $shootingEntity, ?CompoundTag $nbt = null) {
        parent::__construct($location, $shootingEntity, $nbt);
        $this->setScale(0.6);
    }

    protected function onHit(ProjectileHitEvent $event): void {
        $owner = $this->getOwningEntity();

        if (!$owner instanceof Player) {
            return;
        }
        
        if (!$owner->isAlive()) {
            return;
        }

        if ($owner->getWorld()->getId() !== $this->getWorld()->getId()) {
            // Fuck dylan :/
            return;
        }
        $this->getWorld()->addParticle($owner->getPosition(), new EndermanTeleportParticle);
        $this->getWorld()->addSound($owner->getPosition(), new EndermanTeleportSound);

        $vector = $event->getRayTraceResult()->getHitVector();

        $owner->teleport($vector);
        $owner->getServer()->broadcastPackets($owner->getViewers(), [MoveActorAbsolutePacket::create($owner->getId(), $owner->getOffsetPosition($location = $owner->getLocation()), $location->pitch, $location->yaw, $location->yaw, (MoveActorAbsolutePacket::FLAG_TELEPORT | ($owner->onGround ? MoveActorAbsolutePacket::FLAG_GROUND : 0)))]);
        
        $this->getWorld()->addParticle($owner->getPosition(), new EndermanTeleportParticle);
		$this->getWorld()->addSound($owner->getPosition(), new EndermanTeleportSound);
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