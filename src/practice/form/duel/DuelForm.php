<?php

declare(strict_types=1);

namespace practice\form\duel;

use practice\duel\Duel;
use practice\session\Session;
use cosmicpe\form\CustomForm;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\duel\invite\InviteFactory;
use cosmicpe\form\entries\custom\DropdownEntry;

final class DuelForm extends CustomForm {

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
        'Combo' => Duel::TYPE_COMBO
    ];

    public function __construct(Session $session, Session $target) {
        parent::__construct(TextFormat::colorize('&bDuel Invite'));
        $duels = array_keys($this->types);
        $duelsDropdown = new DropdownEntry('Choose Duel', $duels);

        $this->addEntry($duelsDropdown, function(Player $player, DropdownEntry $entry, int $value) use ($duels, $session, $target): void {
            if ($target->getPlayer() === null) {
                $player->sendMessage(TextFormat::colorize('&cPlayer offline.'));
                return;
            }
            $duelName = $duels[$value];

            InviteFactory::create($target, $session, $value);
            $player->sendMessage(TextFormat::colorize('&aYou have sent a party duel invite to ' . $session->getName() . ' in ' . $duelName));
            $target->getPlayer()?->sendMessage(TextFormat::colorize('&aYou have received a ' . $duelName . ' duel invite from ' . $player->getName() . '.'));
        });
    }
}