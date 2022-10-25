<?php

declare(strict_types=1);

namespace practice\form\kit;

use cosmicpe\form\CustomForm;
use cosmicpe\form\entries\custom\InputEntry;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use practice\kit\Kit;
use practice\kit\KitFactory;

final class KitForm extends CustomForm {
    
    public function __construct(Kit $kit) {
        parent::__construct(TextFormat::colorize('&bKit Settings'));
        $horizontalKnockback = new InputEntry('Horizontal Knockback', null, (string) $kit->getHorizontalKnockback());
        $verticalKnockback = new InputEntry('Vertical Knockback', null, (string) $kit->getVerticalKnockback());
        $attackCooldown = new InputEntry('Attack Cooldown', null, (string) $kit->getAttackCooldown());
        
        // Possible hight limiter
        
        $this->addEntry($horizontalKnockback, function (Player $player, InputEntry $entry, string $value) use ($kit): void {
            if (!is_numeric($value)) {
                return;
            }
            $kit->setHorizontalKnockback(floatval($value));
        });
        $this->addEntry($verticalKnockback, function (Player $player, InputEntry $entry, string $value) use ($kit): void {
            if (!is_numeric($value)) {
                return;
            }
            $kit->setVerticalKnockback(floatval($value));
        });
        $this->addEntry($attackCooldown, function (Player $player, InputEntry $entry, string $value) use ($kit): void {
            if (!is_numeric($value)) {
                return;
            }
            $kit->setAttackCooldown(intval($value));
        });
    }
}