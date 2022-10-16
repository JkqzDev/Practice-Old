<?php

declare(strict_types=1);

namespace practice\session\scoreboard;

trait ScoreboardTrait {
    
    private ?ScoreboardBuilder $scoreboard = null;
    
    public function getScoreboard(): ?ScoreboardBuilder {
        return $this->scoreboard;
    }
    
    public function setScoreboard(ScoreboardBuilder $scoreboard): void {
        $this->scoreboard = $scoreboard;
    }
}