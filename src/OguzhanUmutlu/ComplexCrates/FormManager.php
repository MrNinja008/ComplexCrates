<?php


namespace OguzhanUmutlu\ComplexCrates;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\BaseSelector;
use dktapps\pmforms\element\Dropdown;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\Player;

class FormManager {
    private $plugin;
    private $languageManager;
    public function __construct(ComplexCrates $plugin) {
        $this->plugin = $plugin;
        $this->languageManager = $plugin->getLanguageManager();
    }
    public function mainMenu(): MenuForm {
        return new MenuForm(
            $this->languageManager->translate("command-mainmenu-title"),
            $this->languageManager->translate("command-mainmenu-content"),
            [
                new MenuOption($this->languageManager->translate("command-mainmenu-button-create")),
                new MenuOption($this->languageManager->translate("command-mainmenu-button-edit")),
                new MenuOption($this->languageManager->translate("command-mainmenu-button-remove")),
                new MenuOption($this->languageManager->translate("command-mainmenu-button-list")),
                new MenuOption($this->languageManager->translate("command-mainmenu-button-givekey")),
                new MenuOption($this->languageManager->translate("command-mainmenu-button-givekeyall")),
                new MenuOption($this->languageManager->translate("exit"))
            ],
            function(Player $player, int $data): void {
                switch($data){
                    case 0:
                        $player->chat("/".$this->plugin->getCrateCommand()->getName()." ".$this->languageManager->translate("command-subcommands-create"));
                        break;
                    case 1:
                        $player->chat("/".$this->plugin->getCrateCommand()->getName()." ".$this->languageManager->translate("command-subcommands-edit"));
                        break;
                    case 2:
                        $player->chat("/".$this->plugin->getCrateCommand()->getName()." ".$this->languageManager->translate("command-subcommands-remove"));
                        break;
                    case 3:
                        $player->chat("/".$this->plugin->getCrateCommand()->getName()." ".$this->languageManager->translate("command-subcommands-list"));
                        break;
                    case 4:
                        $player->chat("/".$this->plugin->getCrateCommand()->getName()." ".$this->languageManager->translate("command-subcommands-givekey"));
                        break;
                    case 5:
                        $player->chat("/".$this->plugin->getCrateCommand()->getName()." ".$this->languageManager->translate("command-subcommands-givekeyall"));
                        break;
                    default:
                        $player->sendMessage($this->languageManager->translate("menu-exit-message"));
                }
            },
            function(Player $player): void {
                $player->sendMessage($this->languageManager->translate("menu-exit-message"));
            }
        );
    }
    public function createMenu(): CustomForm {
        return new CustomForm(
            $this->languageManager->translate("command-createmenu-title"),
            [
                new Input("name", $this->languageManager->translate("command-createmenu-name"), $this->languageManager->translate("command-createmenu-name-hint"), "")
            ],
            function(Player $player, CustomFormResponse $data): void {
                $player->chat("/".$this->plugin->getCrateCommand()->getName()." ".$this->languageManager->translate("command-subcommands-create")." ".$data->getString("name"));
            },
            function(Player $player): void {
                $player->sendMessage($this->languageManager->translate("menu-exit-message"));
            }
        );
    }
    public function editMenu(): CustomForm {
        $crates = $this->plugin->getCrateManager()->getAllCrateNames();
        return new CustomForm(
            $this->languageManager->translate("command-editmenu-title"),
            [
                new Dropdown("crate", $this->languageManager->translate("command-editmenu-name"), $crates, 0)
            ],
            function(Player $player, CustomFormResponse $data)use($crates): void {
                $player->chat("/".$this->plugin->getCrateCommand()->getName()." ".$this->languageManager->translate("command-subcommands-edit")." ".$crates[array_keys($crates)[$data->getInt("crate")]]);
            },
            function(Player $player): void {
                $player->sendMessage($this->languageManager->translate("menu-exit-message"));
            }
        );
    }
    public function removeMenu(): CustomForm {
        $crates = $this->plugin->getCrateManager()->getAllCrateNames();
        return new CustomForm(
            $this->languageManager->translate("command-removemenu-title"),
            [
                new Dropdown("crate", $this->languageManager->translate("command-removemenu-name"), $crates, 0)
            ],
            function(Player $player, CustomFormResponse $data) use($crates): void {
                $player->chat("/".$this->plugin->getCrateCommand()->getName()." ".$this->languageManager->translate("command-subcommands-remove")." ".$crates[array_keys($crates)[$data->getInt("crate")]]);
            },
            function(Player $player): void {
                $player->sendMessage($this->languageManager->translate("menu-exit-message"));
            }
        );
    }
    public function giveKeyMenu(): CustomForm {
        $crates = $this->plugin->getCrateManager()->getAllCrateNames();
        $players = []; // somewhy array_map gave stupid things
        foreach($this->plugin->getServer()->getOnlinePlayers() as $x) {
            $players[] = $x->getName();
        }
        return new CustomForm(
            $this->languageManager->translate("command-givekeymenu-title"),
            [
                new Dropdown("crate", $this->languageManager->translate("command-givekeymenu-name"), $crates, 0),
                new Dropdown("player", $this->languageManager->translate("command-givekeymenu-player"), $players, 0)
            ],
            function(Player $player, CustomFormResponse $data)use($players,$crates): void {
                $player->chat("/".$this->plugin->getCrateCommand()->getName()." ".$this->languageManager->translate("command-subcommands-givekey")." ".$players[$data->getInt("player")]." ".$crates[array_keys($crates)[$data->getInt("crate")]]);
            },
            function(Player $player): void {
                $player->sendMessage($this->languageManager->translate("menu-exit-message"));
            }
        );
    }
    public function giveKeyAllMenu(): CustomForm {
        $crates = $this->plugin->getCrateManager()->getAllCrateNames();
        return new CustomForm(
            $this->languageManager->translate("command-givekeyallmenu-title"),
            [
                new Dropdown("crate", $this->languageManager->translate("command-givekeyallmenu-name"), $crates, 0)
            ],
            function(Player $player, CustomFormResponse $data)use($crates): void {
                $player->chat("/".$this->plugin->getCrateCommand()->getName()." ".$this->languageManager->translate("command-subcommands-givekeyall")." ".$crates[array_keys($crates)[$data->getInt("crate")]]);
            },
            function(Player $player): void {
                $player->sendMessage($this->languageManager->translate("menu-exit-message"));
            }
        );
    }
}