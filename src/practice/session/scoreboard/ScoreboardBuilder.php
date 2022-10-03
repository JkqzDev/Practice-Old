<?php

declare(strict_types=1);

namespace practice\session\scoreboard;

use practice\Practice;
use practice\session\Session;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\utils\TextFormat;

class ScoreboardBuilder {
    
    public function __construct(
        private Session $session,
        private string $title = '',
        private array $lines = []
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
    }
    
    public function clear(): void {
        $packet = new SetScorePacket();
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
            $lines[] = ' &fOnline: &c' . count($plugin->getServer()->getOnlinePlayers());
            $lines[] = ' &fPlaying: &c' . (count($plugin->getDuelManager()->getDuels()) * 2);
            $lines[] = ' &fIn queues: &c' . count($plugin->getDuelManager()->getQueues());
        }
        $lines[] = '&r&r';
        $lines[] = ' &cmistery.club';
        $lines[] = '&7&r';
        $this->clear();
        
        foreach ($lines as $line) {
            $this->addLine(TextFormat::colorize($line));
        }
    }
}