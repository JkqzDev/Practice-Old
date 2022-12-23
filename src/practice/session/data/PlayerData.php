<?php

declare(strict_types=1);

namespace practice\session\data;

use practice\database\mysql\MySQL;
use practice\database\mysql\queries\InsertAsync;
use practice\database\mysql\queries\SelectAsync;
use practice\database\mysql\queries\UpdateAsync;

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

    private function initData(): void {
        MySQL::runAsync(new SelectAsync('duel_stats', ['xuid' => $this->xuid], '',
                function (array $rows): void {
                    if (count($rows) === 0) {
                        MySQL::runAsync(new InsertAsync('duel_stats', ['xuid' => $this->xuid, 'player' => $this->name]));
                    } else {
                        $row = $rows[0];
                        $this->kills = (int) $row['kills'];
                        $this->deaths = (int) $row['deaths'];
                        $this->killstreak = (int) $row['streak'];
                        $this->elo = (int) $row['elo'];
                    }
                })
        );
    }

    private function updateData(): void {
        if ($this->update) {
            MySQL::runAsync(new UpdateAsync('duel_stats', [
                'player' => $this->name,
                'kills' => $this->kills,
                'deaths' => $this->deaths,
                'streak' => $this->killstreak,
                'elo' => $this->elo
            ], ['xuid' => $this->xuid]));
        }
    }
}