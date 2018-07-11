# JsonApi Bundle for the Symfony framework

[![Build Status](https://scrutinizer-ci.com/g/mikemirten/JsonApi-Bundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/mikemirten/JsonApi-Bundle/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/mikemirten/JsonApi-Bundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mikemirten/JsonApi-Bundle/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mikemirten/JsonApi-Bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mikemirten/JsonApi-Bundle/?branch=master)

This bundle integrates [JsonApi](https://github.com/mikemirten/JsonApi) component with the Symfony framework.
Both, the bundle and the component, requires PHP 7.0 or later.

## How to install
Through composer:

```composer require mikemirten/json-api-bundle```

Add the bundle to kernel of your application:
```php
public function registerBundles()
{
    $bundles = [
        // ...
        new Mikemirten\Bundle\JsonApiBundle\JsonApiBundle()
        // ...
    ];

    return $bundles;
}
```

## How to use
The bundle provides a number of features:

- [JsonAPI document parameter converter](https://github.com/mikemirten/JsonApi-Bundle/wiki/JsonAPI-document-parameter-converter)
- [JsonAPI document response](https://github.com/mikemirten/JsonApi-Bundle/wiki/JsonAPI-document-response)
- [Resource-Based HTTP Client](https://github.com/mikemirten/JsonApi-Bundle/wiki/Resource-based-HTTP-client)
