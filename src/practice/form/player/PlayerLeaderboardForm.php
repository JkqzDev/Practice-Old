<?php

declare(strict_types=1);

namespace practice\form\player;

use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\database\mysql\MySQL;
use practice\database\mysql\queries\QueryAsync;
use practice\database\mysql\queries\SelectAsync;

final class PlayerLeaderboardForm extends SimpleForm {

    public function __construct() {
        parent::__construct(TextFormat::colorize('&dLeaderboards'));
        $eloLeaderboard = new Button(TextFormat::colorize('&7Elo Leaderboard'));
        $killsLeaderboard = new Button(TextFormat::colorize('&7Kills Leaderboard'));
        $deathsLeaderboard = new Button(TextFormat::colorize('&7Deaths Leaderboard'));

        $this->addButton($eloLeaderboard, function(Player $player, int $button_index): void {
            MySQL::runAsync(new SelectAsync('duel_stats', [], 'ORDER BY elo DESC LIMIT 10', function(array $rows) use ($player): void {
                $content = TextFormat::colorize('&c&lTOP 10 ELO PLAYERS&r' . PHP_EOL);

                foreach ($rows as $pos => $data) {
                    $position = $pos + 1;

                    $content .= PHP_EOL . TextFormat::colorize('&c' . $position . '. &f' . $data['player'] . ' &7- &c' . $data['elo']);
                }

                $player->sendForm($this->createEloLeaderboard($content));
            }, 'player, elo'));
        });
        $this->addButton($killsLeaderboard, function(Player $player, int $button_index): void {
            MySQL::runAsync(new SelectAsync('duel_stats', [], 'ORDER BY kills DESC LIMIT 10', function(array $rows) use ($player): void {
                $content = TextFormat::colorize('&c&lTOP 10 KILLS PLAYERS&r' . PHP_EOL);

                foreach ($rows as $pos => $data) {
                    $position = $pos + 1;

                    $content .= PHP_EOL . TextFormat::colorize('&c' . $position . '. &f' . $data['player'] . ' &7- &c' . $data['kills']);
                }

                $player->sendForm($this->createKillsLeaderboard($content));
            }, 'player, kills'));
        });
        $this->addButton($deathsLeaderboard, function(Player $player, int $button_index): void {
            MySQL::runAsync(new SelectAsync('duel_stats', [], 'ORDER BY deaths DESC LIMIT 10', function(array $rows) use ($player): void {
                $content = TextFormat::colorize('&c&lTOP 10 DEATHS PLAYERS&r' . PHP_EOL);

                foreach ($rows as $pos => $data) {
                    $position = $pos + 1;

                    $content .= PHP_EOL . TextFormat::colorize('&c' . $position . '. &f' . $data['player'] . ' &7- &c' . $data['deaths']);
                }

                $player->sendForm($this->createDeathsLeaderboard($content));
            }, 'player, deaths'));
        });
    }

    private function createEloLeaderboard(string $content): SimpleForm {
        return new class($content) extends SimpleForm {

            public function __construct(string $content) {
                parent::__construct(TextFormat::colorize('&cElo Leaderboard'), $content);
            }
        };
    }

    private function createKillsLeaderboard(string $content): SimpleForm {
        return new class($content) extends SimpleForm {

            public function __construct(string $content) {
                parent::__construct(TextFormat::colorize('&cKills Leaderboard'), $content);
            }
        };
    }

    private function createDeathsLeaderboard(string $content): SimpleForm {
        return new class($content) extends SimpleForm {

            public function __construct(string $content) {
                parent::__construct(TextFormat::colorize('&cDeaths Leaderboard'), $content);
            }
        };
    }
}