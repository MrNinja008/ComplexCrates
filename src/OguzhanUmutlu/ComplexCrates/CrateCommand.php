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
            $sender->sendMessage($this->plugin->getLanguageManager()->translate("command-list-message", ["%0" => count($this->plugin->getCrateManager()->getAllCrateNames()), "%1" => implode(", ", $this->plugin->getCrateManager()->getAllCrateNames())]));
        } else if($args[0] == $this->plugin->getLanguageManager()->translate("command-subcommands-givekey")) {
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
            $sender->sendMessage($this->plugin->getCrateManager()->giveKey($args[1], $args[2]));
        } else if($args[0] == $this->plugin->getLanguageManager()->translate("command-subcommands-givekeyall")) {
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
            $sender->sendMessage($this->plugin->getCrateManager()->giveKeyAll($args[1]));
        } else {
            $sender->sendMessage($this->plugin->getLanguageManager()->translate("command-usage"));
        }
    }
}