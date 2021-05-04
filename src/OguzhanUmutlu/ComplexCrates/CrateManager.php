<?php

namespace OguzhanUmutlu\ComplexCrates;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;

class CrateManager {
    private $plugin;
    private $crates;
    private $cratesConfig;
    private $languageManager;
    private $floatingtexts = [];
    public function __construct(ComplexCrates $plugin) {
        $this->plugin = $plugin;
        $this->cratesConfig = $this->plugin->getCrateConfig();
        $this->crates = $this->cratesConfig->getAll();
        $this->languageManager = $this->plugin->getLanguageManager();
    }
    public function createFloatingText(string $name) {
        if(!in_array($name, $this->getAllCrateNames())) return;
        $crateData = $this->getAllCrates()[array_search($name, $this->getAllCrateNames())];
        $crate = $crateData["pos"];
        $particle = new FloatingTextParticle(new Vector3($crate["x"]+.5, $crate["y"]+1, $crate["z"]+.5), str_replace(["%0", "{line}"], [$name, "\n"], $this->plugin->getLanguageManager()->translate("floating-text")));
        if(!$this->plugin->getServer()->isLevelGenerated($crate["level"])) return;
        if(!$this->plugin->getServer()->isLevelLoaded($crate["level"])) {
            $this->plugin->getServer()->loadLevel($crate["level"]);
        }
        $level = $this->plugin->getServer()->getLevelByName($crate["level"]);
        if(!$level->isChunkLoaded($crate["x"] >> 4, $crate["z"] >> 4)) {
            $level->loadChunk($crate["x"] >> 4, $crate["z"] >> 4);
        }
        $level->addParticle($particle, $level->getPlayers());
        $this->floatingtexts[] = ["crate" => $crateData, "particle" => $particle];
    }
    public function getFloatingText(string $name): ?array {
        $result = null;
        foreach($this->floatingtexts as $floatingtext) {
            if($floatingtext["crate"]["name"] == $name) {
                $result = $floatingtext;
            }
        }
        return $result;
    }
    public function removeFloatingText(string $name) {
        if(!$this->getFloatingText($name)) return;
        $dat = $this->getFloatingText($name);
        $ft = $dat["particle"];
        if(!$ft instanceof FloatingTextParticle) return;
        $pos = $dat["crate"]["pos"];
        if(!$this->plugin->getServer()->isLevelGenerated($pos["level"])) return;
        if(!$this->plugin->getServer()->isLevelLoaded($pos["level"])) {
            $this->plugin->getServer()->loadLevel($pos["level"]);
        }
        $level = $this->plugin->getServer()->getLevelByName($pos["level"]);
        if(!$level->isChunkLoaded($pos["x"] >> 4, $pos["z"] >> 4)) {
            $level->loadChunk($pos["x"] >> 4, $pos["z"] >> 4);
        }
        $ft->setInvisible(true);
        $level->addParticle($ft, $level->getPlayers());
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
        $this->createFloatingText($name);
        return $this->languageManager->translate("command-create-success");
    }
    public function editCrate(string $name, Position $pos, array $items): string {
        if(!in_array($name, $this->getAllCrateNames())) return $this->languageManager->translate("crate-not-found", ["%0" => $name]);
        $this->removeCrate($name);
        $this->createCrate($name, $pos, $items);
        return $this->languageManager->translate("command-edit-success");
    }
    public function removeCrate(string $name): string {
        if(!in_array($name, $this->getAllCrateNames())) return $this->languageManager->translate("crate-not-found", ["%0" => $name]);
        unset($this->crates[array_search($name, $this->getAllCrateNames())]);
        $this->cratesConfig->setAll($this->crates);
        $this->cratesConfig->save();
        $this->cratesConfig->reload();
        $this->removeFloatingText($name);
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
