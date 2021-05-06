<?php


namespace OguzhanUmutlu\ComplexCrates;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\Player;

class CrateCommand extends Command {
    private $plugin;
    public function __construct(ComplexCrates $plugin) {
        $this->plugin = $plugin;
        parent::__construct($this->plugin->getConfig()->getNested("crate-command.name"), $this->plugin->getConfig()->getNested("crate-command.description"), null, $this->plugin->getConfig()->getNested("crate-command.aliases"));
        $this->setPermission("complexcrates.command");
        $this->setPermissionMessage($this->plugin->getLanguageManager()->translate("no-perm"));
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if($this->plugin->form == ComplexCrates::FORM_PMFORMS && $sender instanceof Player && !isset($args[0])) {
            $sender->sendForm($this->plugin->getFormManager()->mainMenu());
            return;
        }
        if(!isset($args[0])) $args[0] = "";
        if($args[0] == $this->plugin->getLanguageManager()->translate("command-subcommands-create")) {
            if(!$sender->hasPermission("complexcrates.command.create")) {
                $sender->sendMessage($this->plugin->getLanguageManager()->translate("no-perm"));
                return;
            }
            if($this->plugin->form == ComplexCrates::FORM_PMFORMS && $sender instanceof Player && !isset($args[1])) {
                $sender->sendForm($this->plugin->getFormManager()->createMenu());
                return;
            }
            if(!isset($args[1])) {
                $sender->sendMessage($this->plugin->getLanguageManager()->translate("command-createmenu-error"));
                return;
            }
            $this->plugin->getEventListener()->setCreateBreakPlayer($sender, $args[1]);
            $sender->sendMessage($this->plugin->getLanguageManager()->translate("command-create-breakcrate"));
        } else if($args[0] == $this->plugin->getLanguageManager()->translate("command-subcommands-edit")) {
            if(!$sender->hasPermission("complexcrates.command.edit")) {
                $sender->sendMessage($this->plugin->getLanguageManager()->translate("no-perm"));
                return;
            }
            if(empty($this->plugin->getCrateManager()->getAllCrateNames())) {
                $sender->sendMessage($this->plugin->getLanguageManager()->translate("no-crates"));
                return;
            }
            if($this->plugin->form == ComplexCrates::FORM_PMFORMS && $sender instanceof Player && !isset($args[1])) {
                $sender->sendForm($this->plugin->getFormManager()->editMenu());
                return;
            }
            if(!isset($args[1])) {
                $sender->sendMessage($this->plugin->getLanguageManager()->translate("command-editmenu-error"));
                return;
            }
            $this->plugin->getEventListener()->setEditBreakPlayer($sender, $args[1]);
            $sender->sendMessage($this->plugin->getLanguageManager()->translate("command-edit-breakcrate"));
        } else if($args[0] == $this->plugin->getLanguageManager()->translate("command-subcommands-remove")) {
            if(!$sender->hasPermission("complexcrates.command.remove")) {
                $sender->sendMessage($this->plugin->getLanguageManager()->translate("no-perm"));
                return;
            }
            if(empty($this->plugin->getCrateManager()->getAllCrateNames())) {
                $sender->sendMessage($this->plugin->getLanguageManager()->translate("no-crates"));
                return;
            }
            if($this->plugin->form == ComplexCrates::FORM_PMFORMS && $sender instanceof Player && !isset($args[1])) {
                $sender->sendForm($this->plugin->getFormManager()->removeMenu());
                return;
            }
            if(!isset($args[1])) {
                $sender->sendMessage($this->plugin->getLanguageManager()->translate("command-removemenu-error"));
                return;
            }
            $sender->sendMessage($this->plugin->getCrateManager()->removeCrate($args[1]));
        } else if($args[0] == $this->plugin->getLanguageManager()->translate("command-subcommands-list")) {
            if(!$sender->hasPermission("complexcrates.command.list")) {
                $sender->sendMessage($this->plugin->getLanguageManager()->translate("no-perm"));
                return;
            }
            $sender->sendMessage($this->plugin->getLanguageManager()->translate("command-list-message", ["%0" => count($this->plugin->getCrateManager()->getAllCrateNames()), "%1" => implode(", ", $this->plugin->getCrateManager()->getAllCrateNames())]));
        } else if($args[0] == $this->plugin->getLanguageManager()->translate("command-subcommands-givekey")) {
            if(!$sender->hasPermission("complexcrates.command.givekey")) {
                $sender->sendMessage($this->plugin->getLanguageManager()->translate("no-perm"));
                return;
            }
            if(empty($this->plugin->getCrateManager()->getAllCrateNames())) {
                $sender->sendMessage($this->plugin->getLanguageManager()->translate("no-crates"));
                return;
            }
            if($this->plugin->form == ComplexCrates::FORM_PMFORMS && $sender instanceof Player && !isset($args[1])) {
                $sender->sendForm($this->plugin->getFormManager()->giveKeyMenu());
                return;
            }
            if(!isset($args[1])) {
                $sender->sendMessage($this->plugin->getLanguageManager()->translate("command-givekeymenu-error"));
                return;
            }
            if(!isset($args[2])) {
                $sender->sendMessage($this->plugin->getLanguageManager()->translate("command-givekeymenu-error1"));
                return;
            }
            if(isset(explode(",",$args[1])[1])) {
                foreach(explode(",",$args[1]) as $x) {
                    $this->plugin->getServer()->dispatchCommand($sender, $this->plugin->getConfig()->getNested("crate-command.name")." ".$args[0]." \"".$x."\" \"".$args[2]."\"");
                }
                return;
            }
            if(isset($args[3])){
                foreach(array_slice($args, 2) as $x) {
                    $this->plugin->getServer()->dispatchCommand($sender, $this->plugin->getConfig()->getNested("crate-command.name")." ".$args[0]." \"".$args[1]."\""." "."\"".$x."\"");
                }
                return;
            }
            $sender->sendMessage($this->plugin->getCrateManager()->giveKey($args[1], $args[2]));
        } else if($args[0] == $this->plugin->getLanguageManager()->translate("command-subcommands-givekeyall")) {
            if(!$sender->hasPermission("complexcrates.command.givekey-all")) {
                $sender->sendMessage($this->plugin->getLanguageManager()->translate("no-perm"));
                return;
            }
            if(empty($this->plugin->getCrateManager()->getAllCrateNames())) {
                $sender->sendMessage($this->plugin->getLanguageManager()->translate("no-crates"));
                return;
            }
            if($this->plugin->form == ComplexCrates::FORM_PMFORMS && $sender instanceof Player && !isset($args[1])) {
                $sender->sendForm($this->plugin->getFormManager()->giveKeyAllMenu());
                return;
            }
            if(!isset($args[1])) {
                $sender->sendMessage($this->plugin->getLanguageManager()->translate("command-givekeyallmenu-error"));
                return;
            }
            if(isset($args[2])){
                foreach(array_slice($args, 1) as $x) {
                    $this->plugin->getServer()->dispatchCommand($sender, $this->plugin->getConfig()->getNested("crate-command.name")." ".$args[0]." \"".$x."\"");
                }
                return;
            }
            $sender->sendMessage($this->plugin->getCrateManager()->giveKeyAll($args[1]));
        } else {
            $sender->sendMessage($this->plugin->getLanguageManager()->translate("command-usage"));
        }
    }
}
