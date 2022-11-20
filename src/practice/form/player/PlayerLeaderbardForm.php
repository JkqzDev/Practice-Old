<?php

declare(strict_types=1);

namespace practice\form\player;

use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\database\mysql\MySQL;
use practice\database\mysql\queries\QueryAsync;
use practice\session\Session;
use practice\session\SessionFactory;

final class PlayerLeaderboardForm extends SimpleForm {

    public function __construct() {
        parent::__construct(TextFormat::colorize('&dLeaderboards'));
        $eloLeaderboard = new Button(TextFormat::colorize('&7Elo Leaderboard'));
        $killsLeaderboard = new Button(TextFormat::colorize('&7Kills Leaderboard'));
        $deathsLeaderboard = new Button(TextFormat::colorize('&7Deaths Leaderboard'));

        $this->addButton($eloLeaderboard, function(Player $player, int $button_index): void {
            $player->sendForm($this->createEloLeaderboard($player));
        });
        $this->addButton($killsLeaderboard, function(Player $player, int $button_index): void {});
        $this->addButton($deathsLeaderboard, function(Player $player, int $button_index): void {});
    }

    protected function createEloLeaderboard(Player $player): SimpleForm {
        return new class() extends SimpleForm {

            public function __construct() {
                $content = TextFormat::colorize('&c&lTOP 10 ELO PLAYERS&r' . PHP_EOL);

                MySQL::runAsync(new QueryAsync("SELECT player, kills FROM duel_stats ORDER BY kills DESC LIMIT 10", function (array $rows) use ($content): void {
                    foreach ($rows as $pos => $data) {
                        $position = $pos + 1;

                        $content .= PHP_EOL . TextFormat::colorize('&c' . $position . '. &f' . $data['player'] . ' &7- &c' . $data['kills']);
                    }
                }));
                /*$players = $this->getPlayers();
                arsort($players);
                $content = TextFormat::colorize('&c&lTOP 10 ELO PLAYERS&r' . PHP_EOL);

                $player = array_keys($players);
                $kill = array_values($players);

                for ($pos = 0; $pos < 10; $pos++) {
                    $position = $pos + 1;

                    if (isset($player[$pos])) {
                        $content .= PHP_EOL . TextFormat::colorize('&c' . $position . '. &f' . $player[$pos] . ' &7- &c' . $kill[$pos]);
                    }
                }
                $playerPosition = array_search($session->getName(), $player);
                $content .= PHP_EOL . PHP_EOL . TextFormat::colorize('&7&oYour position: #' . ($playerPosition + 1));*/

                parent::__construct(TextFormat::colorize('&cElo Leaderboard'), $content);
            }

            private function getPlayers(): array {
                $players = [];

                foreach (SessionFactory::getAll() as $session) {
                    assert($session instanceof Session);
                    $players[$session->getName()] = $session->getElo();
                }
                return $players;
            }
        };
    }
}