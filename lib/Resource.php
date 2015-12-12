<?php

namespace JsonI18n;

/**
 * A localization resource
 * @author Vincent Diep
 */
class Resource implements \ArrayAccess, \Countable, \IteratorAggregate, \Serializable
{
    /**
     * The translation values
     * @var string[]
     */
    private $data = array();
    
    /**
     * The locale of the resource
     * @link https://php.net/manual/en/locale.parselocale.php
     * @var string[]
     */
    private $locale;
    
    /**
     * Creates a new translation resource
     * @param string $locale The locale
     * @param string[] $data The translation data
     */
    public function __construct($locale, array $data = array()) {
        $this->data = $data;
        $this->setLocale($locale);
    }
    
    /**
     * Factory method to create a translation resource from a JSON string
     * @param string $json
     * @return \self
     */
    public static function fromJson($json) {
        $input = json_decode($json, true);
        $data = reset($input);
        $locale = key($input);
        
        return new self($locale, $data);
    }
    
    /**
     * Sets the locale of the resource
     * @param string $locale The locale
     * @throws \InvalidArgumentException When provided with an invalid locale
     */
    private function setLocale($locale) {
        $arr = \Locale::parseLocale($locale);
        
        if ($arr === false) {
            throw new \InvalidArgumentException('Invalid locale');
        }
        
        $this->locale = $arr;
    }

    /**
     * Gets the locale of the resource
     * @return string
     */
    public function getLocale() {
        return str_replace('_', '-', \Locale::composeLocale($this->locale));
    }

    /**
     * Gets the language of the resource
     * @return string
     */
    public function getLanguage() {
        return $this->locale['language'];
    }
    
    /**
     * Adds translation values to the resource
     * @param array $data Values to add
     * @param boolean $overwrite Whether the new array should overwrite already existing values
     */
    public function addData(array $data, $overwrite = true) {
        if ($overwrite) {
            $this->data = array_merge($this->data, $data);
        } else {
            $this->data = array_merge($data, $this->data);
        }
    }
    
    /**
     * Returns all translation values
     * @return string[]
     */
    public function getData() {
        return $this->data;
    }
    
    /**
     * Merges a resource into the current resource
     * @param self $resource
     * @param boolean $overwrite Whether the new resource should overwrite already existing values
     */
    public function merge(self $resource, $overwrite = true) {
        if ($this->getLocale() !== $resource->getLocale()) {
            trigger_error('Attempting to merge resources of different locale', \E_USER_NOTICE);
        }
        
        $this->addData($resource->data, (bool) $overwrite);
    }

    /**
     * Gets the number of translation values in the resource
     * @return int
     */
    public function count() {
        return sizeof($this->data);
    }

    /**
     * Gets an iterator for the translation values in the resource
     * @return \ArrayIterator
     */
    public function getIterator() {
        return new \ArrayIterator($this->data);
    }

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset) {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value) {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    /**
     * Serializes the resource into a JSON object
     * @return string
     */
    public function jsonSerialize() {
        $output = array($this->getLocale() => $this->data);
        return json_encode($output, JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG);
    }

    /**
     * Serializes the resource into a JSON object (internally)
     * @return string
     */
    public function serialize() {
        return $this->jsonSerialize();
    }

    /**
     * Unserializes a serialized instance of a resource
     * @param string $serialized
     * @return \self
     */
    public function unserialize($serialized) {
        return static::fromJson($serialized);
    }
}
