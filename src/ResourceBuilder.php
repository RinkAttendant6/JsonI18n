<?php

namespace JsonI18n;

/**
 * Creates translation resources
 * @author Vincent Diep
 */
class ResourceBuilder
{
    /**
     * Creates a Resource from a PHP array
     * @param array $data Resource data
     * @param string $locale The locale
     * @return Resource
     */
    public static function fromArray(array $data, $locale)
    {
        return new Resource($locale, $data);
    }
    
    /**
     * Creates a Resource from a JSON string
     * @param string $input The JSON object
     * @param string $locale The locale
     * @return Resource
     */
    public static function fromString($input, $locale)
    {
        $data = json_decode($input, true);
        if (json_last_error() !== \JSON_ERROR_NONE) {
            if (function_exists('json_last_error_msg')) {
                throw new \InvalidArgumentException(json_last_error_msg(), json_last_error());
            }
            throw new \InvalidArgumentException("Error parsing JSON.", json_last_error());
        }

        return static::fromArray($data, $locale);
    }
    
    /**
     * Creates a Resource from a file
     * @param string $file The path to the file
     * @param string $locale The locale
     * @return Resource
     */
    public static function fromFile($file, $locale)
    {
        if (!is_file($file)) {
            throw new \InvalidArgumentException("$file is not a file");
        }
        
        $contents = file_get_contents($file);
        
        if ($contents === false) {
            throw new \RuntimeException("Error reading file at $file.");
        }
        
        return static::fromString($contents, $locale);
    }
}
