<?php

declare(strict_types=1);

namespace practice\session;

use practice\kit\Kit;
use pocketmine\Server;
use practice\Practice;
use practice\duel\Duel;
use practice\arena\Arena;
use practice\party\Party;
use pocketmine\player\Player;
use practice\duel\DuelFactory;
use pocketmine\player\GameMode;
use pocketmine\entity\Attribute;
use pocketmine\utils\TextFormat;
use practice\item\party\PartyItem;
use practice\duel\queue\PlayerQueue;
use practice\duel\queue\QueueFactory;
use practice\session\data\PlayerData;
use practice\session\inventory\InventoryTrait;
use practice\session\setting\Setting;
use practice\item\arena\JoinArenaItem;
use practice\item\duel\DuelSpectateItem;
use practice\session\handler\HandlerTrait;
use practice\session\setting\SettingTrait;
use practice\item\player\PlayerProfileItem;
use practice\item\duel\queue\RankedQueueItem;
use practice\item\duel\queue\UnrankedQueueItem;
use practice\item\player\PlayerLeaderboardItem;
use practice\session\scoreboard\ScoreboardTrait;
use practice\session\scoreboard\ScoreboardBuilder;
use practice\session\setting\display\DisplaySetting;
use practice\party\duel\DuelFactory as DuelDuelFactory;

final class Session {
    use PlayerData;
    use HandlerTrait;
    use InventoryTrait;
    use SettingTrait;
    use ScoreboardTrait;

    public function __construct(
        private string       $uuid,
        private string       $xuid,
        private string       $name,
        private ?float       $enderpearl = null,
        private ?Arena       $arena = null,
        private ?PlayerQueue $queue = null,
        private ?Duel        $duel = null,
        private ?Party       $party = null,
        public bool          $initialKnockbackMotion = false,
        public bool          $cancelKnockbackMotion = false
    ) {
        $this->setSettings(Setting::create());
        $this->setScoreboard(new ScoreboardBuilder($this, '&l&ePractice&r'));

        $this->initData();
        $this->initSettings();
    }

    public static function create(string $uuid, string $xuid, string $name): self {
        return new self($uuid, $xuid, $name);
    }

    public function getXuid(): string {
        return $this->xuid;
    }

    public function getEnderpearl(): ?float {
        return $this->enderpearl;
    }

    public function getQueue(): ?PlayerQueue {
        return $this->queue;
    }

    public function inLobby(): bool {
        if ($this->inDuel() || $this->inArena()) {
            return false;
        }

        if ($this->inParty()) {
            $party = $this->getParty();

            if ($party->inDuel()) {
                return false;
            }
        }
        return true;
    }

    public function inDuel(): bool {
        return $this->duel !== null;
    }

    public function inArena(): bool {
        return $this->arena !== null;
    }

    public function inParty(): bool {
        return $this->party !== null;
    }

    public function getParty(): ?Party {
        return $this->party;
    }

    public function getCurrentKit(): string {
        $kitName = 'None';

        if ($this->arena !== null) {
            $arena = $this->arena;
            $kitName = $arena->getKit();
        } elseif ($this->duel !== null) {
            $duel = $this->duel;
            $kitName = strtolower(DuelFactory::getName($duel->getTypeId()));
        } elseif ($this->party !== null) {
            $party = $this->party;

            if ($party->inDuel()) {
                $duel = $party->getDuel();
                $kitName = strtolower(DuelDuelFactory::getName($duel->getTypeId()));
            }
        }
        return $kitName;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDuel(): Duel {
        /** @var Duel $duel */
        $duel = $this->duel;
        return $duel;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setEnderpearl(?float $time): void {
        $this->enderpearl = $time;
    }

    public function setArena(?Arena $arena): void {
        $this->arena = $arena;
    }

    public function setQueue(?PlayerQueue $queue): void {
        $this->queue = $queue;
    }

    public function setDuel(?Duel $duel): void {
        $this->duel = $duel;
    }

    public function setParty(?Party $party): void {
        $this->party = $party;
    }

    public function update(): void {
        $this->scoreboard->update();
        $player = $this->getPlayer();
        $enderpearl = $this->enderpearl;

        if ($enderpearl !== null) {
            $time = round($enderpearl - microtime(true), 2);

            if ($time >= 0.00) {
                $times = explode('.', (string) $time);

                $xp = $times[0];
                $progress = 0 . '.' . ($times[1] ?? 0.00);

                $player?->getXpManager()->setXpAndProgress((int) $xp, (float) $progress);
            } else {
                $this->enderpearl = null;
                $player?->getXpManager()->setXpAndProgress(0, 0.00);
            }
        }
        $currentKitEdit = $this->getCurrentKitEdit();

        if ($player !== null && $currentKitEdit !== null) {
            foreach ($player->getServer()->getOnlinePlayers() as $target) {
                if ($target->canSee($player)) {
                    $target->hidePlayer($player);
                }
            }
        }
    }

    public function getPlayer(): ?Player {
        return Server::getInstance()->getPlayerByRawUUID($this->uuid);
    }

    public function join(): void {
        $player = $this->getPlayer();

        if ($player === null) {
            return;
        }
        $scoreboardSetting = $this->getSetting(Setting::SCOREBOARD);

        if ($scoreboardSetting instanceof DisplaySetting && $scoreboardSetting->isEnabled()) {
            $this->scoreboard->spawn();
        }
        $player->setGamemode(GameMode::SURVIVAL());

        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();
        $player->getCursorInventory()->clearAll();

        $player->getEffects()->clear();

        $player->setHealth($player->getMaxHealth());
        $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());

        $this->giveLobbyItems();

        $player->teleport($player->getServer()->getWorldManager()->getDefaultWorld()?->getSpawnLocation());
        $player->setNameTag(TextFormat::colorize('&7' . $player->getName()));

        if (Practice::IS_DEVELOPING) {
            QueueFactory::create($player);
        }
    }

    public function giveLobbyItems(): void {
        $player = $this->getPlayer();

        if ($player === null) {
            return;
        }
        $player->getInventory()->setContents([
            0 => new RankedQueueItem,
            1 => new UnrankedQueueItem,
            2 => new JoinArenaItem,
            4 => new DuelSpectateItem,
            5 => new PartyItem,
            7 => new PlayerLeaderboardItem,
            8 => new PlayerProfileItem
        ]);
    }

    public function quit(): void {
        /** @var Player $player */
        $player = $this->getPlayer();

        if ($this->inQueue()) {
            QueueFactory::remove($player);
        } elseif ($this->inDuel()) {
            $duel = $this->getDuel();

            if ($duel->isPlayer($player)) {
                $duel->finish($player);
            } else {
                $duel->removeSpectator($player);
            }
        } elseif ($this->inArena()) {
            $arena = $this->getArena();
            $arena->quit($player);
        }

        if ($this->inParty()) {
            $party = $this->getParty();
            $party->disband();
        }
        $this->arena = null;
        $this->queue = null;
        $this->duel = null;
        $this->party = null;
        $this->currentKitEdit = null;

        $this->stopSetupArenaHandler();
        $this->stopSetupDuelHandler();
        $this->updatePlayer();
    }

    public function inQueue(): bool {
        return $this->queue !== null;
    }

    public function getArena(): Arena {
        return $this->arena;
    }

    public function updatePlayer(): void {
        $this->updateData();
        $this->updateSettings();
    }

    public function knockback(Player $damager, Kit $kit): void {
        $player = $this->getPlayer();

        if ($player === null) {
            return;
        }

        $horizontalKnockback = $kit->getHorizontalKnockback();
        $verticalKnockback = $kit->getVerticalKnockback();
        $maxHeight = $kit->getMaxHeight();
        $canRevert = $kit->canRevert();

        if ($maxHeight > 0.0 && !$player->isOnGround()) {
            [$max, $min] = $this->clamp($player->getPosition()->getY(), $damager->getPosition()->getY());

            if ($max - $min >= $maxHeight) {
                $verticalKnockback *= 0.75;

                if ($canRevert) {
                    $verticalKnockback *= -1;
                }
            }
        }
        $x = $player->getPosition()->getX() - $damager->getPosition()->getX();
        $z = $player->getPosition()->getZ() - $damager->getPosition()->getZ();
        $f = sqrt($x * $x + $z * $z);

        if ($f <= 0) {
            return;
        }

        if (mt_rand() / mt_getrandmax() > $player->getAttributeMap()->get(Attribute::KNOCKBACK_RESISTANCE)?->getValue()) {
            $f = 1 / $f;

            $motion = clone $player->getMotion();
            $motion->x /= 2;
            $motion->y /= 2;
            $motion->z /= 2;
            $motion->x += $x * $f * $horizontalKnockback;
            $motion->y += $verticalKnockback;
            $motion->z += $z * $f * $horizontalKnockback;

            if ($motion->y > $verticalKnockback) {
                $motion->y = $verticalKnockback;
            }
            $this->initialKnockbackMotion = true;
            $player->setMotion($motion);
        }
    }

    private function clamp(float $first, float $second): array {
        if ($first > $second) {
            return [$first, $second];
        }
        return [$second, $first];
    }
}