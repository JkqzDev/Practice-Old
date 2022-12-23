<?php

declare(strict_types=1);

namespace practice\form\duel;

use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\duel\Duel;
use practice\duel\DuelFactory;
use practice\item\duel\DuelLeaveItem;
use practice\session\SessionFactory;

final class DuelSpectateForm extends SimpleForm {

    public function __construct() {
        parent::__construct(TextFormat::colorize('&3Spectate Duel'));

        $unrankedMatches = array_filter(DuelFactory::getAll(),
            static function (Duel $duel): bool {
                return !$duel->isRanked() && !$duel->isEnded();
            });
        $rankedMatches = array_filter(DuelFactory::getAll(), static function (Duel $duel): bool {
            return $duel->isRanked() && !$duel->isEnded();
        });

        $unrankedButton = new Button(TextFormat::colorize('&7Unranked duels' . PHP_EOL . '&f' . count($unrankedMatches) . ' matches'));
        $rankedButton = new Button(TextFormat::colorize('&7Ranked duels' . PHP_EOL . '&f' . count($rankedMatches) . ' matches'));

        $this->addButton($unrankedButton, function (Player $player, int $button_index) use ($unrankedMatches): void {
            $player->sendForm($this->sendMatchesForm($unrankedMatches, false));
        });

        $this->addButton($rankedButton, function (Player $player, int $button_index) use ($rankedMatches): void {
            $player->sendForm($this->sendMatchesForm($rankedMatches));
        });
    }

    private function sendMatchesForm(array $matches, bool $ranked = true): SimpleForm {
        return new class($matches, $ranked) extends SimpleForm {

            public function __construct(array $matches, bool $ranked) {
                parent::__construct(TextFormat::colorize($ranked ? '&3Ranked Duels' : '&3Unranked Duels'), TextFormat::colorize('&7Select duel for spectate'));

                foreach ($matches as $match) {
                    assert($match instanceof Duel);
                    $button = new Button(TextFormat::colorize('&f' . $match->getFirstSession()->getName() . ' vs ' . $match->getSecondSession()->getName() . PHP_EOL . '&7Gamemode: ' . DuelFactory::getName($match->getTypeId())));

                    $this->addButton($button, static function (Player $player, int $button_index) use ($match): void {
                        if ($match->isEnded()) {
                            return;
                        }
                        $session = SessionFactory::get($player);

                        if ($session === null) {
                            return;
                        }
                        $match->addSpectator($player);
                        $session->setDuel($match);

                        $player->getArmorInventory()->clearAll();
                        $player->getOffHandInventory()->clearAll();
                        $player->getCursorInventory()->clearAll();
                        $player->getInventory()->setContents([8 => new DuelLeaveItem]);

                        $player->setGamemode(GameMode::SPECTATOR());
                        $player->teleport($match->getFirstSession()->getPlayer()->getPosition());
                    });
                }
            }
        };
    }
}