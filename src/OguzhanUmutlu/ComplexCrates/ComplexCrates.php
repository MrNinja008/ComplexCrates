<?php

namespace OguzhanUmutlu\ComplexCrates;

use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use dktapps\pmforms\BaseForm;
use pocketmine\utils\Config;

class ComplexCrates extends PluginBase {
    const COLOR = "§";
    const FORM_NONE = 0;
    const FORM_PMFORMS = 1;
    public $form = self::FORM_NONE;
    private $eventListener;
    private $languageManager;
    private $crateConfig;
    private $crateCommand;
    private $crateManager;
    private $formManager;
    private $menuManager;
    public function onEnable() {
        if($this->getConfig()->getNested("form-enabled") && class_exists(BaseForm::class)) {
            $this->form = self::FORM_PMFORMS;
            //$this->getLogger()->info($this->formatColor("&ePMForms found, PMForms will be used for commands."));
        }
        if(class_exists(InvMenuHandler::class) && !InvMenuHandler::isRegistered()) InvMenuHandler::register($this);
        $this->eventListener = new EventListener($this);
        $this->languageManager = new LanguageManager($this);
        $this->crateConfig = new Config($this->getDataFolder() . "crates.".($this->getConfig()->get("data-type") == "yaml" ? "yml" : $this->getConfig()->get("data-type")), $this->getConfig()->get("data-type") == "json" ? Config::JSON : ($this->getConfig()->get("data-type") == "properties" ? Config::PROPERTIES : Config::YAML), []);
        $this->crateCommand = new CrateCommand($this);
        $this->crateManager = new CrateManager($this);
        $this->formManager = new FormManager($this);
        $this->menuManager = new MenuManager($this);
        $this->getServer()->getCommandMap()->register($this->getName(), $this->crateCommand);
        $this->getServer()->getPluginManager()->registerEvents($this->eventListener, $this);
    }
    public function getEventListener(): EventListener {
        return $this->eventListener;
    }
    public function getLanguageManager(): LanguageManager {
        return $this->languageManager;
    }
    public function getCrateConfig(): Config {
        return $this->crateConfig;
    }
    public function getCrateCommand(): CrateCommand {
        return $this->crateCommand;
    }
    public function getCrateManager(): CrateManager {
        return $this->crateManager;
    }
    public function getFormManager(): FormManager {
        return $this->formManager;
    }
    public function getMenuManager(): MenuManager {
        return $this->menuManager;
    }
    public function formatColor(string $text): string {
        return str_replace("&", self::COLOR, $text);
    }
}
