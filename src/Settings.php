<?php

namespace JsonI18n;

/**
 * Internationalization class to handle date formatting
 * @author Vincent Diep
 * @license https://www.mozilla.org/en-US/MPL/2.0/ MPL-2.0
 */
class Settings
{
    /**
     * The type of fallback
     * @var bool
     */
    protected $fallbackWithKey;

    /**
     * Creates a new JsonI18n\Settings instance
     * @param array $settings The custom settings
     * @throws \InvalidArgumentException If the locale parameter is empty
     */
    public function __construct(array $settings = [])
    {
        if (!$settings) {
            $this->setFallbackWithKey();
            return;
        }

        if (isset($settings['fallbackWithKey'])) {
            $fallbackWithKey = $settings['fallbackWithKey'];
            if (is_bool($fallbackWithKey))
                $this->setFallbackWithKey($fallbackWithKey);
            else
                throw new \InvalidArgumentException('Invalid fallback setting.');
        } else {
            $this->setFallbackWithKey();
        }
    }

    public function setFallbackWithKey(bool $fallback = false): void
    {
        $this->fallbackWithKey = $fallback;
    }

    public function getFallbackWithKey(): bool
    {
        return $this->fallbackWithKey;
    }

}
