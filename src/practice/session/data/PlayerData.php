<?php

declare(strict_types=1);

namespace practice\session\data;

trait PlayerData {

    private int $kills = 0;
    private int $deaths = 0;
    private int $killstreak = 0;

    public function getKills(): int {
        return $this->kills;
    }

    public function getDeaths(): int {
        return $this->deaths;
    }

    public function getKillstreak(): int {
        return $this->killstreak;
    }

    public function addKill(): void {
        $this->kills++;
    }

    public function addDeath(): void {
        $this->deaths++;
    }

    public function addKillstreak(): void {
        $this->killstreak++;
    }

    public function resetKillstreak(): void {
        $this->killstreak = 0;
    }
}