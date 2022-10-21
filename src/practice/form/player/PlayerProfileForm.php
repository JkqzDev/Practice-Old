<?php

declare(strict_types=1);

namespace practice\form\player;

use cosmicpe\form\CustomForm;
use cosmicpe\form\entries\custom\ToggleEntry;
use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\session\Session;
use practice\session\SessionFactory;
use practice\session\setting\display\DisplaySetting;
use practice\session\setting\gameplay\GameplaySetting;

class PlayerProfileForm extends SimpleForm {

    public function __construct() {
        parent::__construct(TextFormat::colorize('&gProfile'));

        $statsButton = new Button(TextFormat::colorize('&7Player stats'));
        $settingsButton = new Button(TextFormat::colorize('&7Player settings'));
        
        $this->addButton($statsButton, function (Player $player, int $button_index): void {
            $this->firstPage($player);
        });
        $this->addButton($settingsButton, function (Player $player, int $button_index): void {
            $this->secondPage($player);
        });
    }

    private function firstPage(Player $player): void {
        $session = SessionFactory::get($player);

        if ($session === null) {
            return;
        }
    }

    private function secondPage(Player $player): void {
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

                        $this->addEntry($toggle, function (Player $player, ToggleEntry $entry, bool $value) use ($session, $setting): void {
                            $setting->setEnabled($value);
                            $setting->execute($session);
                        });
                    } elseif ($setting instanceof GameplaySetting) {
                        $toggle = new ToggleEntry($setting->getName(), $setting->isEnabled());

                        $this->addEntry($toggle, function (Player $player, ToggleEntry $entry, bool $value) use ($setting): void {
                            $setting->setEnabled($value);
                        });
                    }
                }
            }
        };
        $player->sendForm($customForm);
    }
}