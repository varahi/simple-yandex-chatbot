<?php

namespace App\Services;

class TopicService
{
    private array $allowed;
    private array $forbidden;
    private array $settings;

    public function __construct(array $topicsConfig)
    {
        $this->allowed = $topicsConfig['allowed'] ?? [];
        $this->forbidden = $topicsConfig['forbidden'] ?? [];
        $this->settings = $topicsConfig['settings'] ?? [];
    }

    public function isForbidden(string $text): bool
    {
        $text = mb_strtolower($text);

        foreach ($this->forbidden as $word) {
            if ($this->matchWord($text, $word)) {
                return true;
            }
        }
        return false;
    }

    public function isAboutShopping(string $text): bool
    {
        if (in_array('*', $this->allowed)) {
            return true;
        }

        $text = mb_strtolower($text);
        foreach ($this->allowed as $word) {
            if ($this->matchWord($text, $word)) {
                return true;
            }
        }
        return false;
    }


    private function matchWord(string $text, string $word): bool
    {
        if (mb_strlen($word) < ($this->settings['min_word_length'] ?? 3)) {
            return false;
        }

        return $this->settings['use_regex'] ?? false
            ? preg_match("/\b{$word}\b/u", $text)
            : str_contains($text, $word);
    }
}
