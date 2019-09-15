<?php

declare(strict_types=1);

namespace JsonI18n;

use InvalidArgumentException;

/**
 * Creates translation resources
 * @author Vincent Diep
 * @license https://www.mozilla.org/en-US/MPL/2.0/ MPL-2.0
 */
class ResourceBuilder
{
    /**
     * Creates a Resource from a PHP array
     * @param array $data Resource data
     * @param string $locale The locale
     * @return Resource
     */
    public static function fromArray(array $data, string $locale): Resource
    {
        return new Resource($locale, $data);
    }
    
    /**
     * Creates a Resource from a JSON string
     * @param string $input The JSON object
     * @param string $locale The locale
     * @return Resource
     */
    public static function fromString(string $input, string $locale): Resource
    {
        $data = json_decode($input, true);
        if (json_last_error() !== \JSON_ERROR_NONE) {
            if (function_exists('json_last_error_msg')) {
                throw new InvalidArgumentException(json_last_error_msg(), json_last_error());
            }
            throw new InvalidArgumentException("Error parsing JSON.", json_last_error());
        }

        return static::fromArray($data, $locale);
    }
    
    /**
     * Creates a Resource from a file
     * @param string $file The path to the file
     * @param string $locale The locale
     * @return Resource
     */
    public static function fromFile(string $file, string $locale): Resource
    {
        if (!is_file($file)) {
            throw new InvalidArgumentException("$file is not a file");
        }
        
        $contents = file_get_contents($file);
        
        if ($contents === false) {
            throw new \RuntimeException("Error reading file at $file.");
        }
        
        return static::fromString($contents, $locale);
    }
}
