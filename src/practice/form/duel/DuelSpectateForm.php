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
use practice\session\SessionFactory;

final class DuelSpectateForm extends SimpleForm {

    public function __construct() {
        parent::__construct(TextFormat::colorize('&3Spectate Duel'));

        $unrankedMatches = array_filter(DuelFactory::getAll(), function (Duel $duel): bool {
            return !$duel->isRanked() && !$duel->isEnded();
        });
        $rankedMatches = array_filter(DuelFactory::getAll(), function (Duel $duel): bool {
            return $duel->isRanked() && !$duel->isEnded();
        });

        $unrankedButton = new Button(TextFormat::colorize('&7Unranked duels' . PHP_EOL . '&f' . count($unrankedMatches) . ' matches'));
        $rankedButton = new Button(TextFormat::colorize('&7Ranked duels' . PHP_EOL . '&f' . count($rankedMatches) . ' matches'));

        $this->addButton($unrankedButton, function (Player $player, int $button_index) use ($unrankedMatches): void {
            $this->firstPage($player, $unrankedMatches);
        });

        $this->addButton($rankedButton, function (Player $player, int $button_index) use ($rankedMatches): void {
            $this->secondPage($player, $rankedMatches);
        });
    }

    private function firstPage(Player $player, array $matches): void {
        $simpleForm = new class($player, $matches) extends SimpleForm {

            public function __construct(Player $player, array $matches) {
                parent::__construct(TextFormat::colorize('&3Unranked Duels'), TextFormat::colorize('&7Select duel for spectate'));

                foreach ($matches as $match) {
                    assert($match instanceof Duel);
                    $button = new Button(TextFormat::colorize('&f' . $match->getFirstSession()->getName() . ' vs ' . $match->getSecondSession()->getName() . PHP_EOL . '&7Gamemode: ' . DuelFactory::getName($match->getTypeId())));
        
                    $this->addButton($button, function (Player $player, int $button_index) use ($match): void {
                        if ($match->isEnded()) {
                            return;
                        }
                        $session = SessionFactory::get($player);
        
                        if ($session === null) {
                            return;
                        }
                        $session->setDuel($match);
        
                        $player->getInventory()->clearAll();
                        $player->getArmorInventory()->clearAll();
                        $player->getOffHandInventory()->clearAll();
                        $player->getCursorInventory()->clearAll();
        
                        $player->setGamemode(GameMode::SPECTATOR());
                        $player->teleport($match->getWorld()->getSpawnLocation());
                    });
                }
            }
        };
        $player->sendForm($simpleForm);
    }

    private function secondPage(Player $player, array $matches): void {
        $simpleForm = new class($player, $matches) extends SimpleForm {

            public function __construct(Player $player, array $matches) {
                parent::__construct(TextFormat::colorize('&3Ranked Duels'), TextFormat::colorize('&7Select duel for spectate'));

                foreach ($matches as $match) {
                    assert($match instanceof Duel);
                    $button = new Button(TextFormat::colorize('&f' . $match->getFirstSession()->getName() . ' vs ' . $match->getSecondSession()->getName() . PHP_EOL . '&7Gamemode: ' . DuelFactory::getName($match->getTypeId())));
        
                    $this->addButton($button, function (Player $player, int $button_index) use ($match): void {
                        if ($match->isEnded()) {
                            return;
                        }
                        $session = SessionFactory::get($player);
        
                        if ($session === null) {
                            return;
                        }
                        $session->setDuel($match);
        
                        $player->getInventory()->clearAll();
                        $player->getArmorInventory()->clearAll();
                        $player->getOffHandInventory()->clearAll();
                        $player->getCursorInventory()->clearAll();
        
                        $player->setGamemode(GameMode::SPECTATOR());
                        $player->teleport($match->getWorld()->getSpawnLocation());
                    });
                }
            }
        };
        $player->sendForm($simpleForm);
    }
}