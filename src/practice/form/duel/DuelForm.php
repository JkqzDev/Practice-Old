<?php

declare(strict_types=1);

namespace practice\form\duel;

use cosmicpe\form\CustomForm;
use cosmicpe\form\entries\custom\DropdownEntry;
use cosmicpe\form\entries\simple\Button;
use cosmicpe\form\SimpleForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\duel\Duel;
use practice\duel\DuelFactory;
use practice\duel\invite\InviteFactory;
use practice\session\Session;
use practice\world\WorldFactory;

final class DuelForm extends CustomForm {

    /** @var int[] */
    private array $types = [
        'No Debuff' => Duel::TYPE_NODEBUFF,
        'Boxing' => Duel::TYPE_BOXING,
        'Bridge' => Duel::TYPE_BRIDGE,
        'Battle Rush' => Duel::TYPE_BATTLERUSH,
        'Fist' => Duel::TYPE_FIST,
        'Gapple' => Duel::TYPE_GAPPLE,
        'Sumo' => Duel::TYPE_SUMO,
        'Final UHC' => Duel::TYPE_FINALUHC,
        'Cave UHC' => Duel::TYPE_CAVEUHC,
        'Build UHC' => Duel::TYPE_BUILDUHC,
        'Combo' => Duel::TYPE_COMBO,
        'SG' => Duel::TYPE_SG,
        'HG' => Duel::TYPE_HG
    ];

    public function __construct(Session $session, Session $target) {
        parent::__construct(TextFormat::colorize('&bDuel Invite'));
        /** @var string[] $duels */
        $duels = array_keys($this->types);
        $duelsDropdown = new DropdownEntry('Choose Duel', $duels);

        $this->addEntry($duelsDropdown, function (Player $player, DropdownEntry $entry, int $value) use ($duels, $session, $target): void {
            $duelName = DuelFactory::getName($value);
            $newName = explode(' ', $duelName);
            $worlds = WorldFactory::getAllByMode(strtolower(implode('', $newName)));

            if (count($worlds) === 0) {
                $player->sendMessage(TextFormat::colorize('&cDuel don\'t have maps.'));
                return;
            }
            $player->sendForm($this->sendWorldSelectForm($session, $target, $value));
        });
    }

    private function sendWorldSelectForm(Session $session, Session $target, int $duelType): SimpleForm {
        return new class($session, $target, $duelType) extends SimpleForm {

            public function __construct(Session $session, Session $target, int $duelType) {
                parent::__construct(TextFormat::colorize('&bChoose duel map'));
                $duelName = DuelFactory::getName($duelType);
                $newName = explode(' ', $duelName);
                /** @var string[] $worlds */
                $worlds = WorldFactory::getAllByMode(strtolower(implode('', $newName)));

                $this->addButton(new Button('RANDOM MAP'), function (Player $player, int $button_index) use ($session, $target, $duelName, $duelType): void {
                    if ($target->getPlayer() === null) {
                        $player->sendMessage(TextFormat::colorize('&cPlayer offline.'));
                        return;
                    }
                    InviteFactory::create($target, $session, $duelType);
                    $player->sendMessage(TextFormat::colorize('&aYou have sent a duel invite to ' . $target->getName() . ' in ' . $duelName));
                    $target->getPlayer()?->sendMessage(TextFormat::colorize('&aYou have received a ' . $duelName . ' duel invite from ' . $player->getName() . '.'));
                });

                foreach ($worlds as $world) {
                    $button = new Button(strtoupper($world));
                    $this->addButton($button, function (Player $player, int $button_index) use ($session, $target, $duelName, $duelType, $world): void {
                        if ($target->getPlayer() === null) {
                            $player->sendMessage(TextFormat::colorize('&cPlayer offline.'));
                            return;
                        }
                        InviteFactory::create($target, $session, $duelType, $world);
                        $player->sendMessage(TextFormat::colorize('&aYou have sent a duel invite to ' . $target->getName() . ' in ' . $duelName));
                        $target->getPlayer()?->sendMessage(TextFormat::colorize('&aYou have received a ' . $duelName . ' duel invite from ' . $player->getName() . '.'));
                    });
                }
            }
        };
    }
}