<?php

namespace OguzhanUmutlu\ComplexCrates;

use pocketmine\utils\Config;

class LanguageManager {
    private $plugin;
    private $languageFile;
    public function __construct(ComplexCrates $plugin) {
        $this->plugin = $plugin;
        foreach([
            "en_US",
            "ko_KR",
            "rus_RUS",
            "tr_TR",
            "zh_CN",
            "zh_TW"
        ] as $lang) {
            $this->plugin->saveResource("languages/".$lang.".yml");
        }
        $this->languageFile = (new Config($this->plugin->getDataFolder().$this->getLanguage().".yml"))->getAll();
    }
    public function getLanguage(): string {
        return $this->plugin->getConfig()->getNested("language");
    }
    public function getLanguageFile(string $language): array {
        return $this->languageFile;
    }
    public function translate(string $key, array $replace = []): string {
        $a = $this->plugin->formatColor($this->getLanguageFile($this->getLanguage())[$key]);
        return (empty($replace) ? $a : str_replace(array_keys($replace), array_values($replace), $a)) ?? "Language error.";
    }
}
