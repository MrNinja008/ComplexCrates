<?php

namespace OguzhanUmutlu\ComplexCrates;

class LanguageManager {
    private $plugin;
    private $languageFile;
    public function __construct(ComplexCrates $plugin) {
        $this->plugin = $plugin;
        $stream = $this->plugin->getResource("languages/".$this->getLanguage().".yml");
        $this->languageFile = yaml_parse(stream_get_contents($stream));
        fclose($stream);
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
