<?php
declare(strict_types=1);

namespace practice\entity;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\EnderPearl as ProjectileEnderPearl;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\math\RayTraceResult;
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

    public function entityBaseTick(int $tickDiff = 1): bool {
        $hasUpdate = parent::entityBaseTick($tickDiff);
        $owning = $this->getOwningEntity();

        if (!$owning instanceof Player || !$owning->isOnline() || !$owning->isAlive() || $owning->getWorld()->getFolderName() !== $this->getWorld()->getFolderName()) {
            $this->flagForDespawn();
            return true;
        }
        return $hasUpdate;
    }

    protected function onHit(ProjectileHitEvent $event): void {
        $owner = $this->getOwningEntity();

        if (!$owner instanceof Player || !$owner->isAlive() || $owner->getWorld()->getId() !== $this->getWorld()->getId()) {
            return;
        }

        $this->getWorld()->addParticle($owner->getPosition(), new EndermanTeleportParticle);
        $this->getWorld()->addSound($owner->getPosition(), new EndermanTeleportSound);

        $vector = $event->getRayTraceResult()->getHitVector();

        $owner->teleport($vector);
        $owner->getServer()->broadcastPackets(
            $owner->getViewers(),
            [
                MoveActorAbsolutePacket::create($owner->getId(),
                    $owner->getOffsetPosition($location = $owner->getLocation()),
                    $location->pitch,
                    $location->yaw,
                    $location->yaw,
                    (MoveActorAbsolutePacket::FLAG_TELEPORT | ($owner->onGround ? MoveActorAbsolutePacket::FLAG_GROUND : 0)))
            ]
        );

        $this->getWorld()->addParticle($owner->getPosition(), new EndermanTeleportParticle);
        $this->getWorld()->addSound($owner->getPosition(), new EndermanTeleportSound);
    }

    protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult): void {
        parent::onHitBlock($blockHit, $hitResult);
        $owner = $this->getOwningEntity();

        if (!$owner instanceof Player) {
            return;
        }

        if ($blockHit->getId() === 95) {
            $this->getWorld()->addParticle($owner->getPosition(), new EndermanTeleportParticle);
            $this->getWorld()->addSound($owner->getPosition(), new EndermanTeleportSound);

            $vector = $hitResult->getHitVector();
            $owner->teleport($vector);
            $owner->getServer()->broadcastPackets(
                $owner->getViewers(),
                [
                    MoveActorAbsolutePacket::create($owner->getId(),
                        $owner->getOffsetPosition($location = $owner->getLocation()),
                        $location->pitch,
                        $location->yaw,
                        $location->yaw,
                        (MoveActorAbsolutePacket::FLAG_TELEPORT | ($owner->onGround ? MoveActorAbsolutePacket::FLAG_GROUND : 0)))
                ]
            );

            $this->getWorld()->addParticle($owner->getPosition(), new EndermanTeleportParticle);
            $this->getWorld()->addSound($owner->getPosition(), new EndermanTeleportSound);
            return;
        }
    }
}