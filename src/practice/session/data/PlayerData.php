<?php

declare(strict_types=1);

namespace practice\session\data;

trait PlayerData {

    private int $kills = 0;
    private int $deaths = 0;
    private int $killstreak = 0;
    private int $elo = 1000;

    private bool $update = false;

    public function getKills(): int {
        return $this->kills;
    }

    public function getDeaths(): int {
        return $this->deaths;
    }

    public function getKillstreak(): int {
        return $this->killstreak;
    }

    public function getElo(): int {
        return $this->elo;
    }

    public function addKill(): void {
        $this->kills++;
        $this->update = true;
    }

    public function addDeath(): void {
        $this->deaths++;
        $this->update = true;
    }

    public function addKillstreak(): void {
        $this->killstreak++;
        $this->update = true;
    }

    public function addElo(int $value): void {
        $this->elo += $value;
        $this->update = true;
    }

    public function resetKillstreak(): void {
        $this->killstreak = 0;
        $this->update = true;
    }

    public function removeElo(int $value): void {
        $this->elo -= $value;
        $this->update = true;
    }
}