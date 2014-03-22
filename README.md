# JsonI18n [![Build Status](https://travis-ci.org/RinkAttendant6/JsonI18n.svg?branch=master)](https://travis-ci.org/RinkAttendant6/JsonI18n) [![Latest Stable Version](https://poser.pugx.org/rinkattendant6/json-i18n/v/stable.png)](https://packagist.org/packages/rinkattendant6/json-i18n) [![Total Downloads](https://poser.pugx.org/rinkattendant6/json-i18n/downloads.png)](https://packagist.org/packages/rinkattendant6/json-i18n)

Simple PHP internationalization library using JSON data.

## License

[![License](https://poser.pugx.org/rinkattendant6/json-i18n/license.png)](https://packagist.org/packages/rinkattendant6/json-i18n) This library is made available under the [Mozilla Public License, version 2.0](https://www.mozilla.org/MPL/2.0/).

## Usage

### Installation
#### Using Composer
JsonI18n is available through the [Packagist](https://packagist.org/packages/rinkattendant6/json-i18n) repository. You may include JsonI18n in your project by adding this dependency to your composer.phar file:

```json
"require": {
    "rinkattendant6/json-i18n": "dev-master"
}
```

#### Manual installation

1. Download and copy the JsonI18n.php file into your project.
2. Set the `$defaultLanguage` property of the file

### Loading resources
1. Instantiate the `JsonI18n` class.
2. Call the `addResource` method of the class with the path of the file.

Loading a file with keys that already exist will overwrite the values of existing keys.

### Working with localized text

The methods `__` (two underscores), `_e`, `_f`, and `_ef` allow you to work with localized text.

- `__`: Returns localized text
- `_e`: Prints localized text
- `_f`: Returns formatted localized text. Use this method if you have placeholders in your string that need to be passed through `sprintf` or `vsprintf`. Pass the parameter value(s) as the second parameter of the method. The method accepts string, integer, float, NULL, and array (for multiple parameters).
- `_ef`: Prints formatted localized text. See instructions for previous method.
 
By default, these methods will output text in the language specified in the constructor (the fallback language if none is specified). You may explicitly specify the output language as the last parameter of these methods.

### Debugging

To view all of the data in the JsonI18n object, call the `debug()` method.
