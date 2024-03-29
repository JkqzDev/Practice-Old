<?php

declare(strict_types=1);

namespace practice\duel\type;

use pocketmine\block\VanillaBlocks;
use pocketmine\color\Color;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\AxisAlignedBB;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use practice\duel\Duel;
use practice\duel\DuelFactory;
use practice\session\Session;
use practice\world\World;
use practice\world\WorldFactory;

class BattleRush extends Duel {

    private const STARTING_BATTLE = 0;
    private const RUNNING_BATTLE = 1;

    private int $mode = self::RUNNING_BATTLE;

    private int $firstPoints = 0, $secondPoints = 0;
    private AxisAlignedBB $firstPortal, $secondPortal;

    public function handlePlace(BlockPlaceEvent $event): void {
        $block = $event->getBlock();
        $position = $block->getPosition();

        $worldName = $this->worldName;
        $worldData = WorldFactory::get($worldName);

        if ($worldData === null) {
            $event->cancel();
            return;
        }

        $firstPosition = $worldData->getFirstPosition();
        $secondPosition = $worldData->getSecondPosition();

        if (($position->getX() === $firstPosition->getX() && $position->getZ() === $firstPosition->getZ()) || ($position->getX() === $secondPosition->getX() && $position->getZ() === $secondPosition->getZ())) {
            $event->cancel();
            return;
        }

        $firstPortal = $this->firstPortal;
        $secondPortal = $this->secondPortal;

        if ($firstPortal->isVectorInside($position) || $secondPortal->isVectorInside($position)) {
            $event->cancel();
            return;
        }
        $this->blocks[(string) $position] = $block;
    }

    public function handleDamage(EntityDamageEvent $event): void {
        $player = $event->getEntity();

        if (!$player instanceof Player) {
            return;
        }
        $finalHealth = $player->getHealth() - $event->getFinalDamage();

        if ($this->mode === self::STARTING_BATTLE || !$this->isRunning()) {
            $event->cancel();
            return;
        }

        if ($finalHealth <= 0.00) {
            $event->cancel();
            $isFirst = $player->getName() === $this->firstSession->getName();

            $this->giveKit($isFirst ? $this->firstSession : $this->secondSession, $isFirst);
            $this->teleportPlayer($player, $isFirst);
        }
    }

    private function giveKit(Session $session, bool $firstPlayer = true): void {
        $kit = $session->getInventory(strtolower(DuelFactory::getName($this->typeId)));
        $player = $session->getPlayer();

        if ($player === null) {
            return;
        }
        $player->getCursorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();

        $player->setHealth($player->getMaxHealth());
        $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());

        if ($kit !== null) {
            $armorContents = $kit->getRealKit()->getArmorContents();
            $inventoryContents = $kit->getInventoryContents();
            $effects = $kit->getRealKit()->getEffects();
            $color = new Color(0, 0, 255);

            if (!$firstPlayer) {
                $color = new Color(255, 0, 0);
            }

            foreach ($armorContents as $slot => $item) {
                $armorContents[$slot] = $item->setCustomColor($color);
            }

            foreach ($inventoryContents as $slot => $item) {
                if ($item->getId() === ItemIds::WOOL) {
                    $inventoryContents[$slot] = ItemFactory::getInstance()->get($item->getId(), $firstPlayer ? 11 : 14, $item->getCount());
                }
            }
            $player->getArmorInventory()->setContents($armorContents);
            $player->getInventory()->setContents($inventoryContents);
            $effectManager = $player->getEffects();

            foreach ($effects as $effect) {
                $effectManager->add($effect);
            }
        }
    }

    private function teleportPlayer(Player $player, bool $firstPlayer = true): void {
        $worldName = $this->worldName;
        /** @var World $worldData */
        $worldData = WorldFactory::get($worldName);
        $firstPosition = $worldData->getFirstPosition();
        $secondPosition = $worldData->getSecondPosition();

        if ($firstPlayer) {
            $player->teleport(Position::fromObject($firstPosition->add(0.5, 0, 0.5), $this->world));
        } else {
            $player->teleport(Position::fromObject($secondPosition->add(0.5, 0, 0.5), $this->world));
        }
    }

    public function handleMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        $isFirst = $player->getName() === $this->firstSession->getName();

        $ownPortal = $isFirst ? $this->firstPortal : $this->secondPortal;
        $opponentPortal = $isFirst ? $this->secondPortal : $this->firstPortal;

        if ($ownPortal->isVectorInside($player->getPosition())) {
            $block = $player->getWorld()->getBlock($player->getPosition());

            if ($block->getId() === ItemIds::END_PORTAL) {
                $this->teleportPlayer($player, $isFirst);
                $this->giveKit($isFirst ? $this->firstSession : $this->secondSession, $isFirst);
                return;
            }
        }

        if ($opponentPortal->isVectorInside($player->getPosition())) {
            $block = $player->getWorld()->getBlock($player->getPosition());

            if ($block->getId() === ItemIds::END_PORTAL) {
                $this->addPoint($isFirst);
            }
        }
    }

    private function addPoint(bool $isFirstPlayer = true): void {
        if ($isFirstPlayer) {
            $this->firstPoints++;
        } else {
            $this->secondPoints++;
        }
        $firstPlayer = $this->firstSession->getPlayer();
        $secondPlayer = $this->secondSession->getPlayer();

        $this->starting = 5;
        $this->mode = self::STARTING_BATTLE;

        $this->teleportPlayer($firstPlayer);
        $this->teleportPlayer($secondPlayer, false);

        if ($this->firstPoints >= 3) {
            $this->finish($secondPlayer);
            return;
        }

        if ($this->secondPoints >= 3) {
            $this->finish($firstPlayer);
            return;
        }
        $this->giveKit($this->firstSession);
        $this->giveKit($this->secondSession, false);

        $firstPlayer->setImmobile();
        $secondPlayer->setImmobile();

        $title = ($isFirstPlayer ? '&9' . $firstPlayer->getName() : '&c' . $secondPlayer->getName()) . ' &escored!';
        $subTitle = '&9' . $this->firstPoints . ' &7- &c' . $this->secondPoints;

        $firstPlayer->sendTitle(TextFormat::colorize($title), TextFormat::colorize($subTitle));
        $secondPlayer->sendTitle(TextFormat::colorize($title), TextFormat::colorize($subTitle));

        foreach ($this->blocks as $key => $block) {
            $this->world->setBlock($block->getPosition(), VanillaBlocks::AIR());
            unset($this->blocks[$key]);
        }
    }

    public function scoreboard(Player $player): array {
        if ($this->status === self::RUNNING) {
            $firstPoints = $this->firstPoints;
            $secondPoints = $this->secondPoints;

            if ($this->isSpectator($player)) {
                return [
                    ' &9[B] &9' . str_repeat('█', $firstPoints) . '&7' . str_repeat('█', 3 - $firstPoints),
                    ' &c[R] &c' . str_repeat('█', $secondPoints) . '&7' . str_repeat('█', 3 - $secondPoints),
                    ' &r ',
                    ' &fDuration: &e' . gmdate('i:s', $this->running)
                ];
            }
            /** @var Player $opponent */
            $opponent = $this->getOpponent($player);

            return [
                ' &9[B] &9' . str_repeat('█', $firstPoints) . '&7' . str_repeat('█', 3 - $firstPoints),
                ' &c[R] &c' . str_repeat('█', $secondPoints) . '&7' . str_repeat('█', 3 - $secondPoints),
                ' &r ',
                ' &fDuration: &e' . gmdate('i:s', $this->running),
                ' &r&r ',
                ' &aYour ping: ' . $player->getNetworkSession()->getPing(),
                ' &cTheir ping: ' . $opponent->getNetworkSession()->getPing()
            ];
        }
        return parent::scoreboard($player);
    }

    public function update(): void {
        parent::update();

        if ($this->status === self::RUNNING) {
            $firstPlayer = $this->firstSession->getPlayer();
            $secondPlayer = $this->secondSession->getPlayer();

            if ($this->mode === self::STARTING_BATTLE) {
                if ($this->starting <= 0) {
                    $this->mode = self::RUNNING_BATTLE;

                    if ($firstPlayer !== null && $firstPlayer->isImmobile()) {
                        $firstPlayer->setImmobile(false);
                    }

                    if ($secondPlayer !== null && $secondPlayer->isImmobile()) {
                        $secondPlayer->setImmobile(false);
                    }
                    return;
                }
                $this->starting--;
                return;
            }

            if ($firstPlayer !== null && $firstPlayer->getPosition()->getY() < 0) {
                $this->teleportPlayer($firstPlayer);
                $this->giveKit($this->firstSession);
            } elseif ($secondPlayer !== null && $secondPlayer->getPosition()->getY() < 0) {
                $this->teleportPlayer($secondPlayer, false);
                $this->giveKit($this->secondSession, false);
            }
        }
    }

    protected function prepare(): void {
        $world = $this->world;

        $firstSession = $this->firstSession;
        $secondSession = $this->secondSession;

        $world->setTime($this->world::TIME_DAY);
        $world->stopTime();

        $firstPlayer = $firstSession->getPlayer();
        $secondPlayer = $secondSession->getPlayer();

        if ($firstPlayer !== null && $secondPlayer !== null) {
            $firstPlayer->setGamemode(GameMode::SURVIVAL());
            $secondPlayer->setGamemode(GameMode::SURVIVAL());

            $firstPlayer->getArmorInventory()->clearAll();
            $firstPlayer->getInventory()->clearAll();
            $secondPlayer->getArmorInventory()->clearAll();
            $secondPlayer->getInventory()->clearAll();

            $this->giveKit($firstSession);
            $this->giveKit($secondSession, false);

            $this->teleportPlayer($firstPlayer);
            $this->teleportPlayer($secondPlayer, false);

            $firstPlayer->setImmobile();
            $secondPlayer->setImmobile();
        }
    }

    protected function init(): void {
        $worldName = $this->worldName;

        /** @var World $worldData */
        $worldData = WorldFactory::get($worldName);

        /** @var Position $firstPortal */
        $firstPortal = $worldData->getFirstPortal();

        /** @var Position $secondPortal */
        $secondPortal = $worldData->getSecondPortal();

        $this->firstPortal = new AxisAlignedBB(
            (float) $firstPortal->getX(),
            (float) $firstPortal->getY(),
            (float) $firstPortal->getZ(),
            (float) $firstPortal->getX(),
            (float) $firstPortal->getY(),
            (float) $firstPortal->getZ()
        );
        $this->firstPortal->expand(4.0, 30.0, 4.0);

        $this->secondPortal = new AxisAlignedBB(
            (float) $secondPortal->getX(),
            (float) $secondPortal->getY(),
            (float) $secondPortal->getZ(),
            (float) $secondPortal->getX(),
            (float) $secondPortal->getY(),
            (float) $secondPortal->getZ()
        );
        $this->secondPortal->expand(4.0, 30.0, 4.0);

    }
}