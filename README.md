# JsonI18n [![Build Status](https://travis-ci.org/RinkAttendant6/JsonI18n.svg?branch=master)](https://travis-ci.org/RinkAttendant6/JsonI18n) [![Latest Stable Version](https://poser.pugx.org/rinkattendant6/json-i18n/v/stable.png)](https://packagist.org/packages/rinkattendant6/json-i18n) [![Total Downloads](https://poser.pugx.org/rinkattendant6/json-i18n/downloads.png)](https://packagist.org/packages/rinkattendant6/json-i18n)

Simple PHP internationalization library using JSON data.

## License

[![License](https://poser.pugx.org/rinkattendant6/json-i18n/license.png)](https://packagist.org/packages/rinkattendant6/json-i18n) This library is made available under the [Mozilla Public License, version 2.0](https://www.mozilla.org/MPL/2.0/).

## Usage

### Installation
#### Using Composer
JsonI18n is available through the [Packagist](https://packagist.org/packages/rinkattendant6/json-i18n)
repository and can be installed using Composer:

```shell
composer require rinkattendant6/json-i18n
```

### Example

```php
<?php

$t = new \JsonI18n\Translate('en-CA');
// By default, strings will be outputted in the locale given in the constructor
// In this case, Canadian English will be returned unless explicitly stated otherwise

$t->addResource('path/to/file.json');

$t->_ef('greeting', 'Jason'); // prints out "Hello"
```

The `addResource` method can also take an array that represents the JSON object
as described below.

### Sub-resources

The `addSubresource` method works in a similar manner to `addResource`.
Sub-resources can be a JSON file containing one top-level object with key-value
pairs or a PHP array representative of that (a flat, associative array).

Since there is no locale information within a sub-resource, a locale must be
specified when adding a sub-resource:

```php
$t->addSubresource('path/to/another/file.json', 'de-DE');
```

This feature allows you to split your translations into multiple files.

### Resource file format

```json
{
    "@metadata": {
        "author": "Batman",
        "description": "The common stuff",
        "arrayGroups": {
            "label": {
                "en-CA": "label_en_CA",
                "fr-CA": "label_fr_CA"
            }
        }
    },
    "en-CA": {
        "greeting": "Hello %s",
        "bye": "Goodbye!"
    },
    "fr-CA": {
        "greeting": "Bonjour %s",
        "bye": "Au revoir!"
    }
}
```

Since comments are not allowed in JSON, the `@metadata` object can be used for
any data that is not a message. Everything inside `@metadata` is ignored with
the exception of the `arrayGroups` property which is used to flatten arrays.

Every locale is its own property in the JSON file with a value object containing
key-value pairs for translation.

### Setting and getting the default language

The default language is initially specified in the constructor. You may call the
`getLanguage` and `setLanguage` methods to get and set the default language,
respectively.

### Working with localized text

The methods `__` (two underscores), `_e`, `_f`, and `_ef` allow you to work with
localized text.

- `__`: Returns localized text
- `_e`: Prints localized text
- `_f`: Returns formatted localized text. Use this method if you have placeholders in your string that need to be passed through `sprintf` or `vsprintf`. Pass the parameter value(s) as the second parameter of the method. The method accepts string, integer, float, NULL, and array (for multiple parameters).
- `_ef`: Prints formatted localized text. See instructions for previous method.
 
By default, these methods will output text in the language specified in the
constructor. You may explicitly specify the output language as the last parameter
of these methods. For example:

```php
$t->_e('bye', 'fr-CA');
```

### Localizing arrays

In addition to localizing strings, JsonI18n can localize arrays. Values for each language must be in separate keys in the array. The `localizeDeepArray` method will collapse the groups as necessary, returning an array with fewer keys.

```php
$input = [
    'label_en_CA' => 'Name',
    'label_fr_CA' => 'Nom'
];
$output = $t->localizeDeepArray($input, 'label', 0, 'en-CA');

// $output will be: ['label' => 'Name']
```

The `$depth` argument of the method can be set to 1 for two-dimensional arrays, 2 for three-dimensional arrays, and so on. This is useful for localizing data returned from a database via PDO, where different fetch types can return arrays of 1, 2, or 3 dimensions.

### Debugging

To view all of the data in the JsonI18n object, call the `debug()` method.
