<?php

declare(strict_types=1);

namespace practice\party\duel;

use pocketmine\player\GameMode;
use pocketmine\world\Position;
use pocketmine\world\World;
use practice\kit\KitFactory;
use practice\party\Party;
use practice\Practice;
use practice\world\async\WorldDeleteAsync;
use practice\world\WorldFactory;

class Duel {

    public const TYPE_NODEBUFF = 0;
    public const TYPE_GAPPLE = 1;

    public const STARTING = 0;
    public const RUNNING = 1;
    public const RESTARTING = 2;

    public function __construct(
        protected int     $id,
        protected int     $typeId,
        protected string  $worldName,
        protected Party   $firstParty,
        protected Party   $secondParty,
        protected World   $world,
        protected int     $status = self::STARTING,
        protected int     $starting = 5,
        protected int     $running = 0,
        protected int     $restarting = 5,
        protected array   $spectators = [],
        protected array   $blocks = []
    ) {
        $this->prepare();
        $this->init();
    }

    protected function init(): void {

    }

    protected function prepare(): void {
        $worldName = $this->worldName;
        $world = $this->world;

        $firstSession = $this->firstSession;
        $secondSession = $this->secondSession;

        $world->setTime(World::TIME_FULL);
        $world->stopTime();

        $kit = KitFactory::get(strtolower(DuelFactory::getName($this->typeId)));

        /** @var \practice\world\World $worldData */
        $worldData = WorldFactory::get($worldName);
        $firstPosition = $worldData->getFirstPosition();
        $secondPosition = $worldData->getSecondPosition();

        foreach ($this->firstParty->getMembers() as $member) {
            $member->setGamemode(GameMode::SURVIVAL());
            
            $member->getInventory()->clearAll();
            $member->getArmorInventory()->clearAll();
            $member->getCursorInventory()->clearAll();
            $member->getOffHandInventory()->clearAll();

            $kit?->giveTo($member);

            $member->teleport(Position::fromObject($firstPosition->add(0.5, 0, 0.5), $this->world));
        }

        foreach ($this->secondParty->getMembers() as $member) {
            $member->setGamemode(GameMode::SURVIVAL());

            $member->getInventory()->clearAll();
            $member->getArmorInventory()->clearAll();
            $member->getCursorInventory()->clearAll();
            $member->getOffHandInventory()->clearAll();

            $kit?->giveTo($member);

            $member->teleport(Position::fromObject($secondPosition->add(0.5, 0, 0.5), $this->world));
        }
    }

    public function delete(): void {
        Practice::getInstance()->getServer()->getWorldManager()->unloadWorld($this->world);
        Practice::getInstance()->getServer()->getAsyncPool()->submitTask(new WorldDeleteAsync(
            'duel-' . $this->id,
            Practice::getInstance()->getServer()->getDataPath() . 'worlds'
        ));
        DuelFactory::remove($this->id);
    }
}