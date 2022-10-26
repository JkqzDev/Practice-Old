<?php

declare(strict_types=1);

namespace practice\kit\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use practice\form\kit\KitForm;
use practice\kit\KitFactory;

final class KitCommand extends Command {
    
    public function __construct() {
        parent::__construct('kit', 'Command for kit');
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender instanceof Player) {
            return;
        }
        
        if (!isset($args[0])) {
            // Rekit
            return;
        }
        $subCommand = strtolower($args[0]);
        
        if ($sender->hasPermission('kit.command')) {
            if ($subCommand === 'edit') {
                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::colorize('&cUse /kit edit [kitName]'));
                    return;
                }
                $name = $args;
                unset($name[0], $name[1]);

                $kit = KitFactory::get(strtolower(implode(' ', $name)));

                if ($kit === null) {
                    $sender->sendMessage(TextFormat::colorize('&cKit no exists.'));
                    return;
                }
                $form = new KitForm($kit);
                $sender->sendForm($form);
            }
        }
    }
}