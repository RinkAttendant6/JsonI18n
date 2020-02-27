<?php

namespace JsonI18n;

/**
 * Settings that control how the translation works
 * @author Vincent Diep
 * @license https://www.mozilla.org/en-US/MPL/2.0/ MPL-2.0
 */
class Settings
{
    /**
     * If set to false, JsonI18n will show invalid keys
     * @var bool
     */
    protected $strict;

    /**
     * Creates a new JsonI18n\Settings instance
     * @param array $settings The custom settings
     */
    public function __construct(array $settings = [])
    {
        $defaultSettings = ['strict' => true];
        $settings = array_merge($defaultSettings, $settings);

        $this->setStrict((bool) $settings['strict']);
    }

    public function setStrict(bool $strict): void
    {
        $this->strict = $strict;
    }

    public function getStrict(): bool
    {
        return $this->strict;
    }
}
