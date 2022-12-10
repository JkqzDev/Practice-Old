<?php

declare(strict_types=1);

namespace practice\form\player;

use cosmicpe\form\CustomForm;
use cosmicpe\form\SimpleForm;
use pocketmine\player\Player;
use practice\duel\Duel;
use practice\session\Session;
use pocketmine\utils\TextFormat;
use practice\session\SessionFactory;
use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\entries\custom\ToggleEntry;
use practice\session\setting\display\DisplaySetting;
use practice\session\setting\gameplay\GameplaySetting;

class PlayerProfileForm extends SimpleForm {

    public function __construct() {
        parent::__construct(TextFormat::colorize('&gProfile'));

        $statsButton = new Button(TextFormat::colorize('&7Player stats'));
        $settingsButton = new Button(TextFormat::colorize('&7Player settings'));
        $kitEditorButton = new Button(TextFormat::colorize('&7Kit Editor'));

        $this->addButton($statsButton, function(Player $player, int $button_index): void {
            $this->createStatsForm($player);
        });
        $this->addButton($settingsButton, function(Player $player, int $button_index): void {
            $this->createSettingsForm($player);
        });
        $this->addButton($kitEditorButton, function (Player $player, int $button_index): void {

        });
    }

    private function createStatsForm(Player $player): void {
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }
        $simpleForm = new class($session) extends SimpleForm {
            
            public function __construct(Session $session) {
                $description = [
                    '&gKills: &f' . $session->getKills(),
                    '&gDeaths: &f' . $session->getDeaths(),
                    '&gKill-streak: &f' . $session->getKillstreak(),
                    '&gElo: &f' . $session->getElo(),
                    '&r&r'
                ];
                parent::__construct(TextFormat::colorize('&gPlayer Stats'), TextFormat::colorize(implode(PHP_EOL, $description)));
                $exit = new Button(TextFormat::colorize('&cExit'));
                
                $this->addButton($exit);
            }
        };
        $player->sendForm($simpleForm);
    }

    private function createSettingsForm(Player $player): void {
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }
        $customForm = new class($session) extends CustomForm {

            public function __construct(Session $session) {
                parent::__construct(TextFormat::colorize('&gPlayer Settings'));
                $settings = $session->getSettings();

                foreach ($settings as $setting) {
                    if ($setting instanceof DisplaySetting) {
                        $toggle = new ToggleEntry($setting->getName(), $setting->isEnabled());

                        $this->addEntry($toggle, static function(Player $player, ToggleEntry $entry, bool $value) use ($setting, $session): void {
                            $setting->setEnabled($value);
                            $setting->execute($session);
                        });
                    } elseif ($setting instanceof GameplaySetting) {
                        $toggle = new ToggleEntry($setting->getName(), $setting->isEnabled());

                        $this->addEntry($toggle, static function(Player $player, ToggleEntry $entry, bool $value) use ($setting): void {
                            $setting->setEnabled($value);
                        });
                    }
                }
            }
        };
        $player->sendForm($customForm);
    }

    private function createKitEditorForm(Player $player): void {
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }
        $simpleForm = new class($session) extends SimpleForm {

            private array $types = [
                'No Debuff',
                'Boxing',
                'Bridge',
                'Battle Rush',
                'Fist',
                'Gapple',
                'Sumo',
                'Final UHC',
                'Cave UHC',
                'Build UHC',
                'Combo'
            ];

            public function __construct(Session $session) {
                parent::__construct(TextFormat::colorize('&gKit Editor'));

                foreach ($this->types as $name) {
                    $realName = strtolower(str_replace(' ', '', $name));
                    $inventory = $session->getInventory($realName);

                    if ($inventory === null) {
                        continue;
                    }
                    $button = new Button($name);
                    $this->addButton($button, function (Player $player, int $button_index) use ($inventory, $session): void {
                        $session->setCurrentKitEdit($inventory);
                        $player->getInventory()->setContents($inventory->getInventoryContents());
                    });
                }
            }
        };
        $player->sendForm($simpleForm);
    }
}