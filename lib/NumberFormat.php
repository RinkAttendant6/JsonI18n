<?php

namespace JsonI18n;

/**
 * Internationalization class to handle number formatting
 * @author Vincent Diep
 */
class NumberFormat {
    
    /**
     * The locale to display
     * @var string
     */
    protected $locale;

    /**
     * Localization data
     * @var array 
     */
    protected $formatters = array();

    /**
     * Creates a new JsonI18n\NumberFormat instance
     * @param string $locale The default output locale
     * @throws \InvalidArgumentException If the locale parameter is empty
     */
    public function __construct($locale) {
        if(empty($locale)) {
            throw new \InvalidArgumentException('Invalid locale.');
        }
        
        $this->locale = $locale;
    }
    
    public function addResource($file) {
        $contents = file_get_contents($file);
        
        if($contents === false) {
            throw new \RuntimeException("Error reading file at $file.");
        }
        
        $this->processData(json_decode($contents, true));
    }
    
    /**
     * Processes the resource file data
     * @param array $data The data from the resource file
     */
    private function processData(array $data) {
        $formatterTypes = array('decimal', 'currency', 'percent', 'scientific', 'spellout', 'ordinal');
        
        foreach($data['formatters'] as $locale => $f) {
            // Default formats
            foreach($formatterTypes as $ft) {
                $this->formatters[$locale][$ft] = new \NumberFormatter($locale, constant('NumberFormatter::' . strtoupper($ft)));
            }
            
            // Custom formats, if any
            if (is_array($f) && sizeof($f)) {
                foreach($f as $name => $pattern) {
                    $this->formatters[$locale][$name] = new \NumberFormatter($locale, \NumberFormatter::PATTERN_RULEBASED, $pattern);
                }
            }
        }
    }
    
    /**
     * Formats a number
     * @param string|int|float $value The number to format
     * @param string $formatter The name of the formatter pattern to use
     * @param string $locale The locale for format in. Defaults to default locale.
     * @return string The formatted number
     * @throws \InvalidArgumentException If the locale or formatter name is invalid.
     */
    public function format($value, $formatter, $locale = null) {
        
        if($locale === null) {
            $locale = $this->locale;
        }
        
        if(!isset($this->formatters[$locale])) {
            throw new \InvalidArgumentException('Locale data not found.');
        }
        
        if(!isset($this->formatters[$locale][$formatter])) {
            throw new \InvalidArgumentException('Formatter not found for specified locale.');
        }
        
        return $this->formatters[$locale][$formatter]->format($value);
    }
    
    /**
     * Formats a number as a foreign currency value given a 3-digit currency code
     * @param string|int|float $value The number for format
     * @param string $currencyCode The ISO 4217 currency code (e.g. CAD, USD, EUR)
     * @param string $locale The locale for format in. Defaults to default locale.
     * @return string The formatted number
     * @throws \InvalidArgumentException If the locale or formatter name is invalid.
     */
    public function formatForeignCurrency($value, $currencyCode, $locale = null) {
        if($locale === null) {
            $locale = $this->locale;
        }
        
        if(!isset($this->formatters[$locale])) {
            throw new \InvalidArgumentException('Locale data not found.');
        }
        
        return $this->formatters[$locale]['currency']->formatCurrency($value, $currencyCode);
    }
    
    /**
     * Returns a NumberFormatter object
     * @param string $formatter The name of the formatter pattern
     * @param string $locale The locale. Defaults to default locale.
     * @return \NumberFormatter The formatter object
     * @throws \InvalidArgumentException If the locale or formatter name is invalid.
     */
    public function getFormatter($formatter, $locale = null) {
        if($locale === null) {
            $locale = $this->locale;
        }
        
        if(!isset($this->formatters[$locale])) {
            throw new \InvalidArgumentException('Locale data not found.');
        }
        
        if(!isset($this->formatters[$locale][$formatter])) {
            throw new \InvalidArgumentException('Formatter not found for specified locale.');
        }
        
        return $this->formatters[$locale][$formatter];
    }
}