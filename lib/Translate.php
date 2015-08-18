<?php

namespace JsonI18n;

/**
 * Internationalization class to handle resource strings.
 * @author Vincent Diep
 */
class Translate
{
    
    /**
     * The language to display
     * @var string
     */
    protected $lang;
    
    /**
     * Localization data
     * @var array
     */
    protected $data = array(
        'arrayGroups' => array(),
        'values' => array()
    );
    
    /**
     * Creates a new JsonI18n\Translate instance
     * @param string $lang The default output language
     */
    public function __construct($lang) {
        $this->setLanguage($lang);
    }
    
    /**
     * Adds a resource
     * @param mixed $resource The resource to add
     * @param string $type The resource type. Defaults to "file"
     */
    public function addResource($resource, $type = 'file') {
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
                    throw new \InvalidArgumentException("Invalid resource type");
            }
        }
    }

    /**
     * Adds a resource represented in a JSON string
     * @param string $resource The resource as JSON data
     * @throws \InvalidArgumentException If the resource is not valid JSON
     */
    protected function addResourceString($resource) {
        $data = json_decode($resource, true);
        if (json_last_error() !== \JSON_ERROR_NONE) {
            if (function_exists('json_last_error_msg')) {
                throw new \InvalidArgumentException(json_last_error_msg(), json_last_error());
            }
            throw new \InvalidArgumentException("Error parsing JSON.", json_last_error());
        }

        $this->addResourceArray($data);
    }

    /**
     * Adds a resource array
     * @param array $resource The resource array
     */
    protected function addResourceArray(array $resource) {
        $this->data['arrayGroups'] = array_replace_recursive($this->data['arrayGroups'], $resource['arrayGroups']);
        $this->data['values'] = array_replace_recursive($this->data['arrayGroups'], $resource['values']);
    }

    /**
     * Adds a resource file
     * @param string $file The path to the resource file
     * @throws \InvalidArgumentException If the filename provided is not a file
     * @throws \RuntimeException If the file could not be read
     */
    protected function addResourceFile($file) {
        if (!is_file($file)) {
            throw new \InvalidArgumentException("$file is not a file");
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
     * @throws \InvalidArgumentException If the langauge is invalid
     */
    public function setLanguage($lang) {
        if (!is_string($lang) || empty($lang)) {
            throw new \InvalidArgumentException("Invalid language $lang");
        }
        
        $this->lang = $lang;
    }
    
    /**
     * Returns the current language
     * @return string
     */
    public function getLanguage() {
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
    public function __($key, $lang = null) {
        if ($lang === null) {
            $lang = $this->lang;
        }
        
        if (!isset($this->data['values'][$lang])) {
            throw new \OutOfBoundsException("Invalid language: $lang");
        }
        
        if (!isset($this->data['values'][$lang][$key])) {
            throw new \OutOfBoundsException("Invalid key: $key");
        }
        
        return $this->data['values'][$lang][$key];
    }

    /**
     * Prints localized text
     * @param string $key The key (description) of the localized text
     * @param string $lang The output language. Default is default language.
     * @codeCoverageIgnore
     */
    public function _e($key, $lang = null) {
        echo $this->__($key, $lang);
    }
    
    /**
     * Returns "formatted" localized text
     * @param string $key The key (description) of the localized text
     * @param string|array $strings See sprintf (string) and vsprintf (array)
     * @param string $lang The output language. Default is default language.
     * @return string The "formatted" localized text
     * @throws \OutOfBoundsException If the language or key is invalid.
     * @throws \InvalidArgumentException If the formatter strings is not a string or array.
     */
    public function _f($key, $strings, $lang = null) {
        if ($lang === null) {
            $lang = $this->lang;
        }
        
        if (!isset($this->data['values'][$lang])) {
            throw new \OutOfBoundsException("Invalid language: $lang");
        }
        
        if (!isset($this->data['values'][$lang][$key])) {
            throw new \OutOfBoundsException("Invalid key: $key");
        }
        
        if ($strings === null) {
            $strings = '';
        }
        
        if (is_string($strings) || is_float($strings) || is_int($strings)) {
            return sprintf($this->data['values'][$lang][$key], $strings);
        }
        
        if (is_array($strings)) {
            return vsprintf($this->data['values'][$lang][$key], $strings);
        }
        
        throw new \InvalidArgumentException('Strings must be a string or array to return a formatted localized string.');
    }
    
    /**
     * Prints "formatted" localized text
     * @param string $key The key (description) of the localized text
     * @param string|array $strings See sprintf (string) and vsprintf (array)
     * @param string $lang The output language. Default is default language.
     * @codeCoverageIgnore
     */
    public function _ef($key, $strings, $lang = null) {
        echo $this->_f($key, $strings, $lang);
    }
    
    /**
     * Localizes an array by "flattening" a group
     * @param array $arr The array to localize
     * @param string $group The name of the group of fields to flatten
     * @param string $lang The output language. Default is default language.
     * @throws \OutOfBoundsException If the group does not exist or if the array values do not contain the key in a given language
     */
    public function localizeArray(&$arr, $group, $lang = null) {
        if (is_null($arr)) {
            return;
        }
        
        if (!is_array($arr)) {
            throw new \InvalidArgumentException("Array must be an array or null, " . gettype($arr) . " given");
        }
        
        if (!isset($this->data['arrayGroups'][$group])) {
            throw new \OutOfBoundsException("Invalid group: $group");
        }
        
        if ($lang === null) {
            $lang = $this->lang;
        }
        
        if (!isset($this->data['arrayGroups'][$group][$lang])) {
            throw new \OutOfBoundsException("Invalid language: $lang");
        }
        $keep = $this->data['arrayGroups'][$group][$lang];
        
        if (array_key_exists($keep, $arr)) {
            // One dimensional array
            $arr[$group] = $arr[$keep];
            foreach ($this->data['arrayGroups'][$group] as $g) {
                unset($arr[$g]);
            }
        } else {
            // Multi-dimensional array
            foreach ($arr as &$v) {
                if (!array_key_exists($keep, $v)) {
                    throw new \OutOfBoundsException("Invalid array index: $keep");
                }

                $v[$group] = $v[$keep];
                foreach ($this->data['arrayGroups'][$group] as $g) {
                    unset($v[$g]);
                }
            }
            unset($v);
        }
    }

    /**
     * Debug function to print out all localization data
     * @codeCoverageIgnore
     */
    public function debug() {
        print_r($this->__debugInfo());
    }
    
    /**
     * Magic debug method
     * @codeCoverageIgnore
     */
    public function __debugInfo() {
        return $this->data;
    }
}
