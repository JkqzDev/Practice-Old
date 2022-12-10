<?php

declare(strict_types=1);

namespace practice\duel;

use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use practice\Practice;
use pocketmine\world\World;
use pocketmine\player\Player;
use practice\session\Session;
use pocketmine\world\Position;
use pocketmine\player\GameMode;
use pocketmine\utils\TextFormat;
use practice\world\WorldFactory;
use practice\session\SessionFactory;
use practice\world\async\WorldDeleteAsync;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\entity\EntityDamageEvent;

class Duel {

    public const TYPE_NODEBUFF = 0;
    public const TYPE_BOXING = 1;
    public const TYPE_BRIDGE = 2;
    public const TYPE_BATTLERUSH = 3;
    public const TYPE_FIST = 4;
    public const TYPE_GAPPLE = 5;
    public const TYPE_SUMO = 6;
    public const TYPE_FINALUHC = 7;
    public const TYPE_CAVEUHC = 8;
    public const TYPE_BUILDUHC = 9;
    public const TYPE_COMBO = 10;

    public const STARTING = 0;
    public const RUNNING = 1;
    public const RESTARTING = 2;

    static public function calculateElo(int $loser, int $winner): array {
        $expectedScoreA = 1 / (1 + (pow(10, ($loser - $winner) / 400)));
        $expectedScoreB = abs(1 / (1 + (pow(10, ($winner - $loser) / 400))));

        $winnerElo = $winner + intval(32 * (1 - $expectedScoreA));
        $loserElo = $loser + intval(32 * (0 - $expectedScoreB));

        return [
            $winnerElo - $winner,
            abs($loser - $loserElo)
        ];
    }

    public function __construct(
        protected int     $id,
        protected int     $typeId,
        protected string  $worldName,
        protected bool    $ranked,
        protected Session $firstSession,
        protected Session $secondSession,
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

    protected function prepare(): void {
        $worldName = $this->worldName;
        $world = $this->world;

        $firstSession = $this->firstSession;
        $secondSession = $this->secondSession;

        $world->setTime(World::TIME_DAY);
        $world->stopTime();

        /** @var \practice\world\World $worldData */
        $worldData = WorldFactory::get($worldName);
        $firstPosition = $worldData->getFirstPosition();
        $secondPosition = $worldData->getSecondPosition();

        $firstPlayer = $firstSession->getPlayer();
        $secondPlayer = $secondSession->getPlayer();

        if ($firstPlayer !== null && $secondPlayer !== null) {
            $firstPlayer->setGamemode(GameMode::SURVIVAL());
            $secondPlayer->setGamemode(GameMode::SURVIVAL());

            $firstPlayer->getArmorInventory()->clearAll();
            $firstPlayer->getInventory()->clearAll();
            $secondPlayer->getArmorInventory()->clearAll();
            $secondPlayer->getInventory()->clearAll();

            $firstSession->getInventory(strtolower(DuelFactory::getName($this->typeId)))?->giveTo($firstPlayer);
            $secondSession->getInventory(strtolower(DuelFactory::getName($this->typeId)))?->giveTo($secondPlayer);

            $firstPlayer->teleport(Position::fromObject($firstPosition->add(0.5, 0, 0.5), $world));
            $secondPlayer->teleport(Position::fromObject($secondPosition->add(0.5, 0, 0.5), $world));
        }
    }

    protected function init(): void {}

    public function getFirstSession(): Session {
        return $this->firstSession;
    }

    public function getSecondSession(): Session {
        return $this->secondSession;
    }

    public function getWorld(): World {
        return $this->world;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getTypeId(): int {
        return $this->typeId;
    }

    public function isRanked(): bool {
        return $this->ranked;
    }

    public function isRunning(): bool {
        return $this->status === self::RUNNING;
    }

    public function isEnded(): bool {
        return $this->status === self::RESTARTING;
    }

    public function isPlayer(Player $player): bool {
        return $this->firstSession->getXuid() === $player->getXuid() || $this->secondSession->getXuid() === $player->getXuid();
    }

    public function isSpectator(Player $player): bool {
        return isset($this->spectators[spl_object_hash($player)]);
    }

    public function scoreboard(Player $player): array {
        switch ($this->status) {
            case self::STARTING:
                return [
                    ' &fMatch starting'
                ];

            case self::RESTARTING:
                return [
                    ' &fMatch ended'
                ];

            default:
                if ($this->isSpectator($player)) {
                    return [
                        ' &fKit: &e' . DuelFactory::getName($this->typeId),
                        ' &fType: &e' . ($this->ranked ? 'Ranked' : 'Unranked'),
                        ' &r&r',
                        ' &fDuration: &e' . gmdate('i:s', $this->running),
                        ' &fSpectators: &e' . count($this->spectators)
                    ];
                }
                /** @var Player $opponent */
                $opponent = $this->getOpponent($player);

                return [
                    ' &fKit: &e' . DuelFactory::getName($this->typeId),
                    ' &fDuration: &e' . gmdate('i:s', $this->running),
                    ' &r&r',
                    ' &aYour ping: ' . $player->getNetworkSession()->getPing(),
                    ' &cTheir ping: ' . $opponent->getNetworkSession()->getPing()
                ];
        }
    }

    public function getOpponent(Player|Session $player): ?Player {
        $firstSession = $this->firstSession;
        $secondSession = $this->secondSession;

        if ($firstSession->getXuid() === $player->getXuid()) {
            return $secondSession->getPlayer();
        }
        return $firstSession->getPlayer();
    }

    public function addSpectator(Player $player): void {
        $this->spectators[spl_object_hash($player)] = $player;
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

        if ($finalHealth <= 0.00) {
            $event->cancel();
            $this->finish($player);
        }
    }

    public function handleMove(PlayerMoveEvent $event): void {}

    public function finish(Player $loser): void {
        $firstSession = $this->firstSession;
        $secondSession = $this->secondSession;
        $this->loser = $loser->getName();

        if ($loser->getName() === $firstSession->getName()) {
            $winnerElo = $secondSession->getElo();
            $loserElo = $firstSession->getElo();

            $this->winner = $secondSession->getName();
            $secondSession->getPlayer()?->sendTitle(TextFormat::colorize('&l&aWON!&r'), TextFormat::colorize('&7You won the fight!'));
        } else {
            $winnerElo = $firstSession->getElo();
            $loserElo = $secondSession->getElo();

            $this->winner = $firstSession->getName();
            $firstSession->getPlayer()?->sendTitle(TextFormat::colorize('&l&aWON!&r'), TextFormat::colorize('&7You won the fight!'));
        }
        $loser->sendTitle(TextFormat::colorize('&l&cDEFEAT!&r'), TextFormat::colorize('&a' . $this->winner . '&7 won the fight!'));

        if ($this->ranked) {
            $elms = self::calculateElo($loserElo, $winnerElo);

            if ($this->loser === $firstSession->getName()) {
                $secondSession->addElo($elms[0]);
                $firstSession->removeElo($elms[1]);
            } else {
                $firstSession->addElo($elms[0]);
                $secondSession->removeElo($elms[1]);
            }
        }
        $this->log();
        /** @var Player $firstPlayer */
        $firstPlayer = $firstSession->getPlayer();

        /** @var Player $secondPlayer */
        $secondPlayer = $secondSession->getPlayer();

        $firstPlayer->getArmorInventory()->clearAll();
        $firstPlayer->getInventory()->clearAll();
        $secondPlayer->getArmorInventory()->clearAll();
        $secondPlayer->getInventory()->clearAll();

        $firstPlayer->getEffects()->clear();
        $secondPlayer->getEffects()->clear();

        $firstPlayer->setHealth($firstPlayer->getMaxHealth());
        $secondPlayer->setHealth($secondPlayer->getMaxHealth());

        $this->status = self::RESTARTING;
    }

    public function update(): void {
        /** @var Player $firstPlayer */
        $firstPlayer = $this->firstSession->getPlayer();

        /** @var Player $secondPlayer */
        $secondPlayer = $this->secondSession->getPlayer();

        switch ($this->status) {
            case self::STARTING:
                if ($this->starting <= 0) {
                    $this->status = self::RUNNING;

                    if ($firstPlayer->isImmobile()) {
                        $firstPlayer->setImmobile(false);
                    }

                    if ($secondPlayer->isImmobile()) {
                        $secondPlayer->setImmobile(false);
                    }
                    $firstPlayer->sendMessage(TextFormat::colorize('&eMatch started.'));
                    $secondPlayer->sendMessage(TextFormat::colorize('&eMatch started.'));

                    $firstPlayer->sendTitle('Match Started!', TextFormat::colorize('&7The match has begun.'));
                    $secondPlayer->sendTitle('Match Started!', TextFormat::colorize('&7The match has begun.'));
                    return;
                }
                $firstPlayer->sendMessage(TextFormat::colorize('&7The match will be starting in &e' . $this->starting . '&7..'));
                $secondPlayer->sendMessage(TextFormat::colorize('&7The match will be starting in &e' . $this->starting . '&7..'));

                $firstPlayer->sendTitle('Match starting', TextFormat::colorize('&7The match will be starting in &e' . $this->starting . '&7..'));
                $secondPlayer->sendTitle('Match starting', TextFormat::colorize('&7The match will be starting in &e' . $this->starting . '&7..'));
                $this->starting--;
                break;

            case self::RUNNING:
                $this->running++;
                break;

            case self::RESTARTING:
                if ($this->restarting <= 0) {
                    $firstSession = $this->firstSession;
                    $secondSession = $this->secondSession;

                    $firstPlayer?->teleport($firstPlayer->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                    $secondPlayer?->teleport($secondPlayer->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());

                    $firstSession->giveLobbyItems();
                    $secondSession->giveLobbyItems();

                    $firstSession->setDuel(null);
                    $secondSession->setDuel(null);

                    foreach ($this->spectators as $spectator) {
                        $session = SessionFactory::get($spectator);
                        $session?->setDuel(null);
                        $session?->giveLobbyItems();

                        $spectator->setGamemode(GameMode::SURVIVAL());
                        $spectator->teleport($spectator->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
                    }
                    $this->delete();
                    return;
                }
                $this->restarting--;
                break;
        }
    }

    protected function delete(): void {
        Practice::getInstance()->getServer()->getWorldManager()->unloadWorld($this->world);
        Practice::getInstance()->getServer()->getAsyncPool()->submitTask(new WorldDeleteAsync(
            'duel-' . $this->id,
            Practice::getInstance()->getServer()->getDataPath() . 'worlds'
        ));
        DuelFactory::remove($this->id);
    }

    protected function log(): void {
        $webhook = new Webhook($this->ranked ? Practice::getInstance()->getConfig()->get('webhook-ranked-duels', '') : Practice::getInstance()->getConfig()->get('webhook-unranked-duels', ''));
        $message = new Message();

        if ($this->winner === $this->firstSession->getName()) {
            $winner = $this->firstSession;
            $loser = $this->secondSession;
        } else {
            $winner = $this->secondSession;
            $loser = $this->firstSession;
        }
        $message->setUsername('Kresu Practice');

        if (!$this->ranked) {
            $message->setContent(
                '**UNRANKED - ' . DuelFactory::getName($this->typeId) . '**' . PHP_EOL .
                '__Winner:__ ' . $winner->getName() . PHP_EOL .
                '__Loser:__ ' . $loser->getName() . PHP_EOL .
                '__Time:__ ' . gmdate('i:s', $this->running) . PHP_EOL .
                '------------------'
            );
        } else {
            $message->setContent(
                '**RANKED - ' . DuelFactory::getName($this->typeId) . '**' . PHP_EOL .
                '__Winner:__ ' . $winner->getName() . ' [' . $winner->getElo() . ']' . PHP_EOL .
                '__Loser:__ ' . $loser->getName() . ' [' . $loser->getElo() . ']' . PHP_EOL .
                '__Time:__ ' . gmdate('i:s', $this->running) . PHP_EOL .
                '------------------'
            );
        }
        $webhook->send($message);
    }
}