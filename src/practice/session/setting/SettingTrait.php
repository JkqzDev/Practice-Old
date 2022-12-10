<?php

declare(strict_types=1);

namespace practice\session\setting;

use practice\database\mysql\MySQL;
use practice\database\mysql\queries\InsertAsync;
use practice\database\mysql\queries\SelectAsync;
use practice\database\mysql\queries\UpdateAsync;
use practice\session\setting\display\DisplaySetting;
use practice\session\setting\gameplay\GameplaySetting;

trait SettingTrait {

    private array $settings = [];

    private function initSettings(): void {
        MySQL::runAsync(new SelectAsync('player_settings', ['xuid' => $this->xuid], '',
                function (array $rows): void {
                    if (count($rows) === 0) {
                        MySQL::runAsync(new InsertAsync('player_settings', ['xuid' => $this->xuid, 'player' => $this->name]));
                    } else {
                        $row = $rows[0];
                        $this->getSetting(Setting::SCOREBOARD)?->setEnabled((bool) $row[Setting::SCOREBOARD]);
                        $this->getSetting(Setting::CPS_COUNTER)?->setEnabled((bool) $row[Setting::CPS_COUNTER]);
                        $this->getSetting(Setting::AUTO_RESPAWN)?->setEnabled((bool) $row[Setting::AUTO_RESPAWN]);
                    }
                })
        );
    }

    private function updateSettings(): void {
        $scoreboardValue = (int) $this->getSetting(Setting::SCOREBOARD)->isEnabled();
        $autoRespawnValue = (int) $this->getSetting(Setting::AUTO_RESPAWN)->isEnabled();
        $cpsCounterValue = (int) $this->getSetting(Setting::CPS_COUNTER)->isEnabled();

        MySQL::runAsync(new UpdateAsync('player_settings', [
            'player' => $this->name,
            'scoreboard' => $scoreboardValue,
            'auto_respawn' => $autoRespawnValue,
            'cps_counter' => $cpsCounterValue
        ], ['xuid' => $this->xuid]));
    }

    public function getSettings(): array {
        return $this->settings;
    }

    public function setSettings(array $settings): void {
        $this->settings = $settings;
    }

    public function getSetting(string $name): Setting|GameplaySetting|DisplaySetting|null {
        return $this->settings[$name] ?? null;
    }
}