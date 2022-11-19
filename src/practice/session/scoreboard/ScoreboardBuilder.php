<?php

declare(strict_types=1);

namespace practice\session\scoreboard;

use practice\Practice;
use practice\session\Session;
use practice\duel\DuelFactory;
use pocketmine\utils\TextFormat;
use practice\session\SessionFactory;
use practice\duel\queue\PlayerQueue;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;

class ScoreboardBuilder {

    public function __construct(
        private Session $session,
        private string  $title = '',
        private array   $lines = []
    ) {}

    public function spawn(): void {
        $packet = SetDisplayObjectivePacket::create(
            SetDisplayObjectivePacket::DISPLAY_SLOT_SIDEBAR,
            $this->session->getPlayer()?->getName(),
            TextFormat::colorize($this->title),
            'dummy',
            SetDisplayObjectivePacket::SORT_ORDER_ASCENDING
        );
        $this->session->getPlayer()?->getNetworkSession()->sendDataPacket($packet);
    }

    public function despawn(): void {
        $pk = RemoveObjectivePacket::create(
            $this->session->getPlayer()?->getName()
        );
        $this->session->getPlayer()?->getNetworkSession()->sendDataPacket($pk);
    }

    public function update(): void {
        $plugin = Practice::getInstance();
        $session = $this->session;
        $player = $this->session->getPlayer();

        if ($player === null || !$player->isOnline()) {
            return;
        }
        $lines = [
            '&7'
        ];

        if ($session->inLobby()) {
            $playing = array_filter(SessionFactory::getAll(), static function(Session $target): bool {
                return !$target->inLobby() && $target->getPlayer() !== null;
            });
            $lines[] = ' &fOnline: &c' . count($plugin->getServer()->getOnlinePlayers());
            $lines[] = ' &fPlaying: &c' . count($playing);

            if ($session->inQueue()) {
                /** @var PlayerQueue $queue */
                $queue = $session->getQueue();

                $lines[] = '&7&r&r&r';
                $lines[] = $queue->isRanked() ? ' &cRanked ' . DuelFactory::getName($queue->getDuelType()) : ' &cUnranked ' . DuelFactory::getName($queue->getDuelType());
                $lines[] = ' &fTime: &c' . gmdate('i:s', $queue->getTime());
            }
        } elseif ($session->inArena()) {
            $arena = $session->getArena();

            $lines = array_merge($lines, $arena->scoreboard($player));
        } elseif ($session->inDuel()) {
            $duel = $session->getDuel();

            $lines = array_merge($lines, $duel->scoreboard($player));
        }
        $lines[] = '&r&r';
        $lines[] = ' &7misty.lol';
        $lines[] = '&7&r';
        $this->clear();

        foreach ($lines as $line) {
            $this->addLine(TextFormat::colorize($line));
        }
    }

    public function clear(): void {
        $packet = new SetScorePacket;
        $packet->entries = $this->lines;
        $packet->type = SetScorePacket::TYPE_REMOVE;
        $this->session->getPlayer()?->getNetworkSession()->sendDataPacket($packet);
        $this->lines = [];
    }

    public function addLine(string $line, ?int $id = null): void {
        $id = $id ?? count($this->lines);

        $entry = new ScorePacketEntry;
        $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;

        if (isset($this->lines[$id])) {
            $pk = new SetScorePacket;
            $pk->entries[] = $this->lines[$id];
            $pk->type = SetScorePacket::TYPE_REMOVE;
            $this->session->getPlayer()?->getNetworkSession()->sendDataPacket($pk);
            unset($this->lines[$id]);
        }
        $entry->scoreboardId = $id;
        $entry->objectiveName = $this->session->getPlayer()?->getName();
        $entry->score = $id;
        $entry->actorUniqueId = $this->session->getPlayer()?->getId();
        $entry->customName = $line;
        $this->lines[$id] = $entry;

        $packet = new SetScorePacket;
        $packet->entries[] = $entry;
        $packet->type = SetScorePacket::TYPE_CHANGE;
        $this->session->getPlayer()?->getNetworkSession()->sendDataPacket($packet);
    }
}