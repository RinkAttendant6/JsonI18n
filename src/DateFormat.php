<?php

namespace JsonI18n;

/**
 * Internationalization class to handle date formatting
 * @author Vincent Diep
 * @license https://www.mozilla.org/en-US/MPL/2.0/ MPL-2.0
 */
class DateFormat
{
    /**
     * The locale to display
     * @var string
     */
    protected $locale;

    /**
     * Localization data
     * @var \IntlDateFormatter[][]
     */
    protected $formatters = [];

    /**
     * Creates a new JsonI18n\DateFormat instance
     * @param string $locale The default output locale
     * @throws \InvalidArgumentException If the locale parameter is empty
     */
    public function __construct(string $locale)
    {
        if (empty($locale)) {
            throw new \InvalidArgumentException('Invalid locale.');
        }
        
        $this->locale = $locale;
    }

    /**
     * Adds a resource
     * @param string $file The resource to add
     */
    public function addResource(string $file): void
    {
        $contents = file_get_contents($file);
        
        if ($contents === false) {
            throw new \RuntimeException("Error reading file at $file.");
        }
        
        $this->processData(json_decode($contents, true));
    }
    
    /**
     * Processes the resource file data
     * @param array $data The data from the resource file
     */
    private function processData(array $data): void
    {
        foreach ($data['formatters'] as $locale => $f) {
            if (!isset($this->formatters[$locale])) {
                $this->formatters[$locale] = array();
            }
            
            foreach ($f as $formatter => $d) {
                $calendar = \IntlDateFormatter::GREGORIAN;
                if (isset($d['calendar']) && $d['calendar'] === 'traditional') {
                    $calendar = \IntlDateFormatter::TRADITIONAL;
                }
                
                $this->formatters[$locale][$formatter] = new \IntlDateFormatter($locale, null, null, null, $calendar, $d['pattern']);
            }
        }
    }
    
    /**
     * Formats a date/time
     * @param \DateTime|string $datetime The date/time to format. If a string is passed, it will be used to create a DateTime object.
     * @param string $formatter The name of the formatter pattern to use
     * @param string $locale The locale for format in. Defaults to default locale.
     * @return string The formatted date
     * @throws \InvalidArgumentException If the locale or formatter name is invalid.
     */
    public function format($datetime, string $formatter, ?string $locale = null): string
    {
        if ($locale === null) {
            $locale = $this->locale;
        }
        
        if (!($datetime instanceof \DateTime)) {
            $datetime = new \DateTime($datetime);
        }
        
        if (!isset($this->formatters[$locale])) {
            throw new \InvalidArgumentException('Locale data not found.');
        }
        
        if (!isset($this->formatters[$locale][$formatter])) {
            throw new \InvalidArgumentException('Formatter not found for specified locale.');
        }
        
        return $this->formatters[$locale][$formatter]->format($datetime->getTimestamp());
    }
    
    /**
     * Returns a IntlDateFormatter object
     * @param string $formatter The name of the formatter pattern
     * @param string $locale The locale. Defaults to default locale.
     * @return \IntlDateFormatter The formatter object
     * @throws \InvalidArgumentException If the locale or formatter name is invalid.
     */
    public function getFormatter(string $formatter, ?string $locale = null): \IntlDateFormatter
    {
        if ($locale === null) {
            $locale = $this->locale;
        }
        
        if (!isset($this->formatters[$locale])) {
            throw new \InvalidArgumentException('Locale data not found.');
        }
        
        if (!isset($this->formatters[$locale][$formatter])) {
            throw new \InvalidArgumentException('Formatter not found for specified locale.');
        }
        
        return $this->formatters[$locale][$formatter];
    }
    
    /**
     * Debug function
     * @codeCoverageIgnore
     */
    public function debug(): void
    {
        foreach ($this->formatters as $locale => $formats) {
            echo "\n# $locale\n";
            foreach ($formats as $name => $format) {
                echo "## $name\n";
                echo "Locale: " . $format->getLocale(\Locale::VALID_LOCALE) . "\n" .
                     "DateType: " . $format->getDateType() . "\n" .
                     "TimeType: " . $format->getTimeType() . "\n" .
                     "Calendar: " . $format->getCalendar() . "\n" .
                     "Pattern: " . $format->getPattern() . "\n\n";
            }
        }
    }
    
    /**
     * Magic debug method
     * @codeCoverageIgnore
     */
    public function __debugInfo(): array
    {
        return $this->formatters;
    }
}
