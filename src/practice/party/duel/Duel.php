<?php

declare(strict_types=1);

namespace practice\party\duel;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
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
    public const TYPE_FIST = 2;
    public const TYPE_COMBO = 3;
    public const TYPE_BUILDUHC = 4;
    public const TYPE_CAVEUHC = 5;
    public const TYPE_FINALUHC = 6;

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
        protected string  $winner = '',
        protected string  $loser = '',
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

    public function getId(): int {
        return $this->id;
    }

    public function getTypeId(): int {
        return $this->typeId;
    }

    public function isRunning(): bool {
        return $this->status === self::RUNNING;
    }

    public function isEnded(): bool {
        return $this->status === self::RESTARTING;
    }

    public function getOpponent(Player $player): Party {
        $firstParty = $this->firstParty;
        $secondParty = $this->secondParty;

        if ($firstParty->isMember($player)) {
            return $secondParty;
        }
        return $firstParty;
    }

    public function isPlayer(Player $player): bool {
        if ($this->isSpectator($player)) {
            return false;
        }
        return $this->firstParty->isMember($player) || $this->secondParty->isMember($player);
    }

    public function isSpectator(Player $player): bool {
        return isset($this->spectators[spl_object_hash($player)]);
    }

    public function scoreboard(Player $player): array {
        switch ($this->status) {
            case self::STARTING:
                return [
                    ' &fParty match starting'
                ];

            case self::RESTARTING:
                return [
                    ' &fParty match ended'
                ];
            
            default:
                if ($this->isSpectator($player)) {
                    return [
                        ' &fKit: &c' . DuelFactory::getName($this->typeId),
                        ' &r&r',
                        ' &fDuration: &c' . gmdate('i:s', $this->running),
                        ' &fSpectators: &c' . count($this->spectators)
                    ];
                }
                $opponent = $this->getOpponent($player);
                $players = array_filter($opponent->getMembers(), function (Player $player): bool {
                    return !$this->isSpectator($player);
                });

                return [
                    ' &fKit: &c' . DuelFactory::getName($this->typeId),
                    ' &fDuration: &c' . gmdate('i:s', $this->running),
                    ' &r&r',
                    ' &fPlayers: &c' . count($players)
                ];
        }
    }

    public function addSpectator(Player $player): void {
        $this->spectators[spl_object_hash($player)] = $player;

        $player->getArmorInventory()->clearAll();
        $player->getInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
        $player->getOffHandInventory()->clearAll();

        $player->setGamemode(GameMode::SPECTATOR());
    }

    public function removeSpectator(Player $player): void {
        $hash = spl_object_hash($player);

        if (!$this->isSpectator($player)) {
            return;
        }
        unset($this->spectators[$hash]);
    }

    public function handleBreak(BlockBreakEvent $event): void {
        $block = $event->getBlock();

        if (!isset($this->blocks[(string)$block->getPosition()])) {
            $event->cancel();
            return;
        }
        unset($this->blocks[(string)$block->getPosition()]);
    }

    public function handlePlace(BlockPlaceEvent $event): void {
        $block = $event->getBlock();

        $this->blocks[(string)$block->getPosition()] = $block;
    }

    public function handleDamage(EntityDamageEvent $event): void {
        $player = $event->getEntity();

        if (!$player instanceof Player) {
            return;
        }
        $finalHealth = $player->getHealth() - $event->getFinalDamage();

        if (!$this->isRunning()) {
            $event->cancel();
            return;
        }
        $d = null;

        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();

            if (!$damager instanceof Player) {
                return;
            }

            if (!$this->isPlayer($damager)) {
                $event->cancel();
                return;
            }
            $playerOpponent = $this->getOpponent($player);
            $damagerOpponent = $this->getOpponent($damager);

            if ($playerOpponent->getName() === $damagerOpponent->getName()) {
                $event->cancel();
                return;
            }
            $d = $damager;
        }

        if ($finalHealth <= 0.00) {
            $event->cancel();
            $this->firstParty->broadcastMessage(TextFormat::colorize('&c' . $player->getName() . ($d === null ? ' &edied' : ' &ewas slain by &c' . $d->getName())));
            $this->secondParty->broadcastMessage(TextFormat::colorize('&c' . $player->getName() . ($d === null ? ' &edied' : ' &ewas slain by &c' . $d->getName())));

            $this->addSpectator($player);
            $this->checkWinner();
        }
    }

    public function handleMove(PlayerMoveEvent $event): void {
        // Nothing
    }

    public function checkWinner(): void {
        $firstParty = array_filter($this->firstParty->getMembers(), function (Player $player): bool {
            return !$this->isSpectator($player);
        });
        $secondParty = array_filter($this->secondParty->getMembers(), function (Player $player): bool {
            return !$this->isSpectator($player);
        });

        if (count($firstParty) === 0) {
            $this->finish($this->firstParty);
        } elseif (count($secondParty) === 0) {
            $this->finish($this->secondParty);
        }
    }

    public function finish(Party $loser): void {
        $this->loser = $loser->getName();

        if ($this->firstParty->getName() === $loser->getName()) {
            $this->winner = $this->secondParty->getName();

            $this->secondParty->broadcastTitle(TextFormat::colorize('&l&aWON!&r'), TextFormat::colorize('&7Your party won the fight!'));
        } else {
            $this->winner = $this->firstParty->getName();
            $this->firstParty->broadcastTitle(TextFormat::colorize('&l&aWON!&r'), TextFormat::colorize('&7Your party won the fight!'));
        }
        $loser->broadcastTitle(TextFormat::colorize('&l&cDEFEAT!&r'), TextFormat::colorize('&a' . $this->winner . '&7 won the fight!'));
        $members = array_merge($this->firstParty->getMembers(), $this->secondParty->getMembers());

        foreach ($members as $member) {
            if ($member->isOnline()) {
                $member->getArmorInventory()->clearAll();
                $member->getInventory()->clearAll();
                $member->getOffHandInventory()->clearAll();
                $member->getCursorInventory()->clearAll();

                $member->setHealth($member->getMaxHealth());
            }
        }
        $this->status = self::RESTARTING;
    }

    public function update(): void {
        $firstParty = $this->firstParty;
        $secondParty = $this->secondParty;

        switch ($this->status) {
            case self::STARTING:
                $members = array_merge($firstParty->getMembers(), $secondParty->getMembers());

                if ($this->starting <= 0) {
                    $this->status = self::RUNNING;

                    foreach ($members as $member) {
                        if ($member->isOnline()) {
                            if ($member->isImmobile()) {
                                $member->setImmobile(false);
                                $member->sendMessage(TextFormat::colorize('&cMatch started.'));
                                $member->sendTitle('Match Started!', TextFormat::colorize('&7The match has begun.'));
                            }
                        }
                    }
                    return;
                }
                foreach ($members as $member) {
                    if ($member->isOnline()) {
                        $member->sendMessage(TextFormat::colorize('&7The match will be starting in &c' . $this->starting . '&7..'));
                        $member->sendTitle('Match starting', TextFormat::colorize('&7The match will be starting in &c' . $this->starting . '&7..'));
                    }
                }
                $this->starting--;
                break;

            case self::RUNNING:
                $this->running++;
                break;

            case self::RESTARTING:
                if ($this->restarting <= 0) {
                    foreach ($firstParty->getMembers() as $member) {
                        $member->setGamemode(GameMode::SURVIVAL());
                        $member->setHealth($member->getMaxHealth());
                        $member->getInventory()->clearAll();
                        $member->getArmorInventory()->clearAll();
                        $member->teleport($member->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());

                        $firstParty->giveItems($member);
                    }

                    foreach ($secondParty->getMembers() as $member) {
                        $member->setGamemode(GameMode::SURVIVAL());
                        $member->setHealth($member->getMaxHealth());
                        $member->getInventory()->clearAll();
                        $member->getArmorInventory()->clearAll();
                        $member->teleport($member->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());

                        $secondParty->giveItems($member);
                    }
                    $firstParty->setDuel(null);
                    $secondParty->setDuel(null);

                    $this->delete();
                    return;
                }
                $this->restarting--;
                break;
        }
    }

    public function delete(): void {
        Practice::getInstance()->getServer()->getWorldManager()->unloadWorld($this->world);
        Practice::getInstance()->getServer()->getAsyncPool()->submitTask(new WorldDeleteAsync(
            'party-duel-' . $this->id,
            Practice::getInstance()->getServer()->getDataPath() . 'worlds'
        ));
        DuelFactory::remove($this->id);
    }
}