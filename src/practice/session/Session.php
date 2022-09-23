<?php

declare(strict_types=1);

namespace practice\session;

use practice\session\scoreboard\ScoreboardBuilder;
use pocketmine\player\Player;
use pocketmine\Server;

class Session {
    
    static public function create(string $uuid): self {
        return new self($uuid);
    }
    
    private ScoreboardBuilder $scoreboard;
    
    public function __construct(
        private string $uuid
    ) {
        $this->scoreboard = new ScoreboardBuilder($this, '&l&cMisty Practice');
    }
    
    public function getPlayer(): ?Player {
        return Server::getInstance()->getPlayerByRawUUID($this->uuid);
    }
    
    public function update(): void {
        $this->scoreboard->update();
    }
}