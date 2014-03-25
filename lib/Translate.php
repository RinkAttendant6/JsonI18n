<?php
namespace JsonI18n;

/**
 * Internationalization class to handle resource strings.
 * @author Vincent Diep
 */
class Translate {
    
    /**
     * The language to display
     * @var string
     */
    protected $lang;
    
    /**
     * Localization data
     * @var array
     */
    protected $data = array();
    
    /**
     * Creates a new JsonI18n\Translate instance
     * @param string $lang The default output language
     * @throws \InvalidArgumentException If the language parameter is empty
     */
    public function __construct($lang) {
        if(empty($lang)) {
            throw new \InvalidArgumentException('Invalid language.');
        }
        $this->lang = $lang;
    }
    
    /**
     * Adds a resource file
     * @param string $file The path to the resource file
     * @throws \RuntimeException If the file could not be read
     */
    public function addResource($file) {
        $contents = file_get_contents($file);
        
        if($contents === false) {
            throw new \RuntimeException("Error reading file at $file.");
        }
        
        $this->data = array_replace_recursive($this->data, json_decode($contents, true));
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
        if($lang === null) {
            $lang = $this->lang;
        }
        
        if(!isset($this->data['values'][$lang])) {
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
        if($lang === null) {
            $lang = $this->lang;
        }
        
        if(!isset($this->data['values'][$lang])) {
            throw new \OutOfBoundsException("Invalid language: $lang");
        }
        
        if (!isset($this->data['values'][$lang][$key])) {
            throw new \OutOfBoundsException("Invalid key: $key");
        }
        
        if($strings === null) {
            $strings = '';
        }
        
        if(is_string($strings) || is_float($strings) || is_int($strings)) {
            return sprintf($this->data['values'][$lang][$key], $strings);
        }
        
        if(is_array($strings)) {
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
    public function localizeArray(array &$arr, $group, $lang = null) {
        if(!isset($this->data['arrayGroups'][$group])) {
            throw new \OutOfBoundsException("Invalid group: $group");
        }
        
        if($lang === null) {
            $lang = $this->lang;
        }
        
        if(!isset($this->data['arrayGroups'][$group][$lang])) {
            throw new \OutOfBoundsException("Invalid language: $lang");
        }
        $keep = $this->data['arrayGroups'][$group][$lang];
        
        foreach($arr as &$v) {
        
            if(!isset($v[$keep])) {
                throw new \OutOfBoundsException("Invalid array index: $keep");
            }
        
            $v[$group] = $v[$keep];
            foreach($this->data['arrayGroups'][$group] as $g) {
                unset($v[$g]);
            }
        }
        unset($v);
    }

    /**
     * Debug function to print out all localization data
     * @codeCoverageIgnore
     */
    public function debug() {
        print_r($this->data);
    }
}