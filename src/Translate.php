<?php

declare(strict_types=1);

namespace JsonI18n;

use InvalidArgumentException;

/**
 * Internationalization class to handle resource strings.
 * @author Vincent Diep
 * @license https://www.mozilla.org/en-US/MPL/2.0/ MPL-2.0
 */
class Translate
{
    /**
     * The default output language
     * @var string
     */
    protected $lang;
    
    /**
     * Set of groups to flatten
     * @var array[]
     */
    protected $arrayGroups;
    
    /**
     * Set of resources
     * @var Resource[]
     */
    protected $data;
    
    /**
     * Set of settings
     * @var array[]
     */
    protected $settings;
    
    /**
     * Creates a new JsonI18n\Translate instance
     * @param string $lang The default output language
     */
    public function __construct(string $lang)
    {
        $this->setSettings();
        $this->setLanguage($lang);
    }

    public function setSettings(array $settings = []): void
    {
        $this->settings = new Settings($settings);
    }

    /**
     * Adds a resource
     * @param mixed $resource The resource to add
     * @param string $type The resource type. Defaults to "file"
     */
    public function addResource($resource, string $type = 'file'): void
    {
        if (is_array($resource)) {
            $this->addResourceArray($resource);
            return;
        }

        if (is_string($resource)) {
            switch ($type) {
                case 'json':
                    $this->addResourceString($resource);
                    break;
                case 'file':
                    $this->addResourceFile($resource);
                    break;
                default:
                    throw new InvalidArgumentException("Invalid resource type");
            }
        }
    }

    /**
     * Adds a resource represented in a JSON string
     * @param string $resource The resource as JSON data
     * @throws InvalidArgumentException If the resource is not valid JSON
     */
    protected function addResourceString(string $resource): void
    {
        $data = json_decode($resource, true);
        if (json_last_error() !== \JSON_ERROR_NONE) {
            if (function_exists('json_last_error_msg')) {
                throw new InvalidArgumentException(json_last_error_msg(), json_last_error());
            }
            throw new InvalidArgumentException("Error parsing JSON.", json_last_error());
        }

        $this->addResourceArray($data);
    }

    /**
     * Adds a resource array
     * @param array $resource The resource array
     */
    protected function addResourceArray(array $resource): void
    {
        foreach ($resource as $locale => $value) {
            if ($locale === '@metadata') {
                $this->parseMetadata($value);
                continue;
            }
            $this->addSubresource($value, $locale);
        }
    }
    
    /**
     * Adds a sub-resource
     * @param mixed $subresource The sub-resource value
     * @param string $locale
     */
    public function addSubresource($subresource, string $locale): void
    {
        if ($subresource instanceof Resource) {
            $resource = $subresource;
        } elseif (is_string($subresource)) {
            $resource = ResourceBuilder::fromFile($subresource, $locale);
        } elseif (is_array($subresource)) {
            $resource = ResourceBuilder::fromArray($subresource, $locale);
        } else {
            throw new InvalidArgumentException('Invalid subresource');
        }

        if (isset($this->data[$locale])) {
            $this->data[$locale]->merge($resource);
        } else {
            $this->data[$locale] = $resource;
        }
    }
    
    /**
     * Parses resource file metadata
     * @param array $metadata The metadata
     */
    protected function parseMetadata(array $metadata): void
    {
        if (isset($metadata['arrayGroups'])) {
            foreach ($metadata['arrayGroups'] as $name => $values) {
                if (!isset($this->arrayGroups[$name])) {
                    $this->arrayGroups[$name] = array();
                }
                
                foreach ($values as $locale => $keyName) {
                    $this->arrayGroups[$name][$locale] = $keyName;
                }
            }
        }
    }

    /**
     * Adds a resource file
     * @param string $file The path to the resource file
     * @throws InvalidArgumentException If the filename provided is not a file
     * @throws \RuntimeException If the file could not be read
     */
    protected function addResourceFile(string $file): void
    {
        if (!is_file($file)) {
            throw new InvalidArgumentException("$file is not a file");
        }
        
        $contents = file_get_contents($file);
        
        if ($contents === false) {
            throw new \RuntimeException("Error reading file at $file.");
        }
        
        $this->addResourceString($contents);
    }
    
    /**
     * Sets the default language
     * @param string $lang The language
     * @throws InvalidArgumentException If the langauge is invalid
     */
    public function setLanguage(string $lang): void
    {
        if (!is_string($lang) || empty($lang)) {
            throw new InvalidArgumentException("Invalid language $lang");
        }
        
        $this->lang = $lang;
    }
    
    /**
     * Returns the current language
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->lang;
    }
    
    /**
     * Returns localized text
     * Use this if you need to pass the localized string to another function such as ucwords() or ucfirst()
     * @param string $key The key (description) of the localized text
     * @param string $lang The output language. Default is default language.
     * @return string The localized text
     * @throws \OutOfBoundsException If the language or key is invalid.
     * @throws \LogicException If the value of the key is an array.
     */
    public function __(string $key, ?string $lang = null): string
    {
        if ($lang === null) {
            $lang = $this->lang;
        }
        
        if (!isset($this->data[$lang])) {
            throw new \OutOfBoundsException("Invalid language: $lang");
        }
        
        if (!isset($this->data[$lang][$key])) {
            if (!$this->settings->getStrict()) {
                return $key;
            }
            throw new \OutOfBoundsException("Invalid key: $key");
        }
        
        return $this->data[$lang][$key];
    }

    /**
     * Prints localized text
     * @param string $key The key (description) of the localized text
     * @param string $lang The output language. Default is default language.
     * @codeCoverageIgnore
     */
    public function _e(string $key, ?string $lang = null): void
    {
        echo $this->__($key, $lang);
    }
    
    /**
     * Returns "formatted" localized text
     * @param string $key The key (description) of the localized text
     * @param string|array $strings See sprintf (string) and vsprintf (array)
     * @param string $lang The output language. Default is default language.
     * @return string The "formatted" localized text
     * @throws \OutOfBoundsException If the language or key is invalid.
     * @throws InvalidArgumentException If the formatter strings is not a string or array.
     */
    public function _f(string $key, $strings, ?string $lang = null): string
    {
        if ($lang === null) {
            $lang = $this->lang;
        }
        
        if (!isset($this->data[$lang])) {
            throw new \OutOfBoundsException("Invalid language: $lang");
        }
        
        if (!isset($this->data[$lang][$key])) {
            if (!$this->settings->getStrict()) {
                return $key;
            }
            throw new \OutOfBoundsException("Invalid key: $key");
        }
        
        if ($strings === null) {
            $strings = '';
        }
        
        if (is_string($strings) || is_float($strings) || is_int($strings)) {
            return sprintf($this->data[$lang][$key], $strings);
        }
        
        if (is_array($strings)) {
            return vsprintf($this->data[$lang][$key], $strings);
        }
        
        throw new InvalidArgumentException('Strings must be a string or array to return a formatted localized string.');
    }
    
    /**
     * Prints "formatted" localized text
     * @param string $key The key (description) of the localized text
     * @param string|array $strings See sprintf (string) and vsprintf (array)
     * @param string $lang The output language. Default is default language.
     * @codeCoverageIgnore
     */
    public function _ef(string $key, $strings, ?string $lang = null): void
    {
        echo $this->_f($key, $strings, $lang);
    }

    /**
     * Localizes a multidimensional array
     * @param array $arr The array to localize
     * @param string $group The name of the group of fields to flatten
     * @param int $depth The depth of the array
     * @param string $lang The output language. Default is default language.
     * @return array
     */
    public function localizeDeepArray(?array $arr, string $group, int $depth = 1, ?string $lang = null): ?array
    {
        if ($arr === null) {
            return null;
        }
        
        if ($depth < 0) {
            throw new InvalidArgumentException("Depth must be a non-negative integer, $depth given");
        }
        
        if ($depth) {
            foreach ($arr as $key => &$value) {
                if (!is_array($value)) {
                    throw new \LengthException("Exceeded depth when localizing deep array");
                }
                $value = $this->localizeDeepArray($value, $group, $depth - 1, $lang);
            }
            unset($value);
            return $arr;
        }
        
        return $this->flatten($arr, $group, $lang);
    }

    /**
     * Flattens an array group
     * @param array $arr The array to localize
     * @param string $group The name of the group of fields to flatten
     * @param string $lang The output language. Default is default language.
     * @return array
     * @throws \OutOfBoundsException If the group does not exist or if the array values do not contain the key in a given language
     */
    private function flatten(?array $arr, string $group, ?string $lang = null): ?array
    {
        if ($arr === null) {
            return null;
        }

        if (!isset($this->arrayGroups[$group])) {
            throw new \OutOfBoundsException("Invalid group: $group");
        }

        if ($lang === null) {
            $lang = $this->lang;
        }

        if (!isset($this->arrayGroups[$group][$lang])) {
            throw new \OutOfBoundsException("Invalid language: $lang");
        }
        
        $keep = $this->arrayGroups[$group][$lang];
        
        if (!array_key_exists($keep, $arr)) {
            throw new \OutOfBoundsException("Invalid array index: $keep");
        }

        $arr[$group] = $arr[$keep];
        foreach ($this->arrayGroups[$group] as $g) {
            unset($arr[$g]);
        }
        
        return $arr;
    }

    /**
     * Debug function to print out all localization data
     * @codeCoverageIgnore
     */
    public function debug(): void
    {
        print_r($this->__debugInfo());
    }
    
    /**
     * Magic debug method
     * @codeCoverageIgnore
     */
    public function __debugInfo(): array
    {
        return $this->data;
    }
}
