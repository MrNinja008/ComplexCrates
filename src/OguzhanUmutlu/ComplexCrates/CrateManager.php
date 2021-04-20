<?php

namespace OguzhanUmutlu\ComplexCrates;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\types\Enchant;
use pocketmine\Player;

class CrateManager {
    private $plugin;
    private $crates;
    private $cratesConfig;
    private $languageManager;
    public function __construct(ComplexCrates $plugin) {
        $this->plugin = $plugin;
        $this->cratesConfig = $this->plugin->getCrateConfig();
        $this->crates = $this->cratesConfig->getAll();
        $this->languageManager = $this->plugin->getLanguageManager();
    }

    public function getAllCrates(): array {
        return $this->crates;
    }
    public function getAllCrateNames(): array {
        return array_map(function($n){return $n["name"];}, $this->getAllCrates());
    }
    public function createCrate(string $name, Position $pos, array $items): string {
        if(in_array($name, $this->getAllCrateNames())) return $this->languageManager->translate("crate-already-exists", ["%0" => $name]);
        $this->crates[] = [
            "name" => $name,
            "pos" => [
                "x" => $pos->getFloorX(),
                "y" => $pos->getFloorY(),
                "z" => $pos->getFloorZ(),
                "level" => $pos->getLevel()->getFolderName()
            ],
            "items" => $items
        ];
        $this->cratesConfig->setAll($this->crates);
        $this->cratesConfig->save();
        $this->cratesConfig->reload();
        return $this->languageManager->translate("command-create-success");
    }
    public function editCrate(string $name, Position $pos, array $items): string {
        if(!in_array($name, $this->getAllCrateNames())) return $this->languageManager->translate("crate-not-found", ["%0" => $name]);
        $this->removeCrate($name);
        $this->crates[] = [
            "name" => $name,
            "pos" => [
                "x" => $pos->getFloorX(),
                "y" => $pos->getFloorY(),
                "z" => $pos->getFloorZ(),
                "level" => $pos->getLevel()->getFolderName()
            ],
            "items" => $items
        ];
        $this->cratesConfig->setAll($this->crates);
        $this->cratesConfig->save();
        $this->cratesConfig->reload();
        return $this->languageManager->translate("command-edit-success");
    }
    public function removeCrate(string $name): string {
        if(!in_array($name, $this->getAllCrateNames())) return $this->languageManager->translate("crate-not-found", ["%0" => $name]);
        unset($this->crates[array_search($name, $this->getAllCrateNames())]);
        $this->cratesConfig->setAll($this->crates);
        $this->cratesConfig->save();
        $this->cratesConfig->reload();
        return $this->languageManager->translate("command-remove-success");
    }
    public function giveKey($player, string $crate): string {
        if(!in_array($crate, $this->getAllCrateNames())) return $this->languageManager->translate("crate-not-found", ["%0" => $crate]);
        if(!$player instanceof Player) $player = $this->plugin->getServer()->getPlayerExact($player);
        if(!$player || !$player instanceof Player) {
            return $this->plugin->getLanguageManager()->translate("command-givekeymenu-error2");
        }
        $key = $this->getKey($crate);
        if(!$player->getInventory()->canAddItem($key)) return $this->languageManager->translate("command-givekey-full");
        $player->getInventory()->addItem($key);
        return $this->languageManager->translate("command-givekey-success");
    }
    public function giveKeyAll(string $crate): string {
        $key = $this->getKey($crate);
        if(!in_array($crate, $this->getAllCrateNames())) return $this->languageManager->translate("crate-not-found", ["%0" => $crate]);
        foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
            if($player->getInventory()->canAddItem($key)) {
                $player->getInventory()->addItem($key);
            }
        }
        return $this->languageManager->translate("command-givekeyall-success");
    }
    public function getKey(string $crate): Item {
        $key = new Item(Item::TRIPWIRE_HOOK);
        $key->setCustomName(str_replace("%0", $crate, $this->plugin->getLanguageManager()->translate("item-name")));
        $key->setLore([str_replace("%0", $crate, $this->plugin->getLanguageManager()->translate("item-lore"))]);
        $key->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(0), 1));
        $key->removeEnchantment(0);
        return $key;
    }
}