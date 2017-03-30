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

### JsonAPI-document parameter converter
The converter allows to receive an instance of JsonAPI-document as an argument of method inside of your controller.

```php
use Mikemirten\Component\JsonApi\Document\SingleResourceDocument;

class UserController
{
    public function postAction(SingleResourceDocument $document)
    {
        $resource = $document->getResource();
        
        $user = new User();
        
        $user->setFirstName($resource->getAttribute('firstName'));
        $user->setLastName($resource->getAttribute('lastName'));
    }
}
```

In a case of document provided through request contains different structure (a collection of resources or is empty data-document) a BadRequestHttpException exception will be thrown. If you're expectiong more than one type of document, use AbstractDocument type:

```php
use Mikemirten\Component\JsonApi\Document\AbstractDocument;
use Mikemirten\Component\JsonApi\Document\SingleResourceDocument;

class UserController
{
    public function postAction(AbstractDocument $document)
    {
        if ($document instanceof SingleResourceDocument) {
            $resource = $document->getResource();

            $user = new User();

            $user->setFirstName($resource->getAttribute('firstName'));
            $user->setLastName($resource->getAttribute('lastName'));
        }
    }
}
```

More information about document you can find inside of [JsonApi repository](https://github.com/mikemirten/JsonApi).

### JsonAPI-document response
The bundle allows to return an istance of JsonAPI-document as a return. In this case it will be automaticaly serialized and used as a response's body.

```php
use Mikemirten\Component\JsonApi\Document\ResourceObject;
use Mikemirten\Component\JsonApi\Document\SingleResourceDocument;

class UserController
{
    public function getAction()
    {
        // ...
        
        $resource = new ResourceObject();
        
        $resource->setAttribute('firstName', $user->getFirstName());
        $resource->setAttribute('lastName', $user->getLastName());
    
        $document = new SingleResourceDocument(resource);
        $document->setMetadataAttribute('requestId', $requestId);
        
        return $document;
    }
}
```

If you don't need to interact with document itself but only with resource, it is possible to return an instance of resource.

```php
use Mikemirten\Component\JsonApi\Document\ResourceObject;

class UserController
{
    public function getAction()
    {
        // ...
        
        $resource = new ResourceObject();
        
        $resource->setAttribute('firstName', $user->getFirstName());
        $resource->setAttribute('lastName', $user->getLastName());
        
        return $resource;
    }
}
```

Also there is a "shortcut" to return a single error as a part of document.

```php
use Mikemirten\Component\JsonApi\Document\ErrorObject;

class UserController
{
    public function getAction()
    {
        // ...
        
        $error = new ErrorObject();
        
        $error->setId('E345');
        $error->setTitle('Out of range');
        
        return $error;
    }
}
```

An instance of the same document implementation works for both: request and response purposes, so thechnically, it is possible to return just received document:
```php
use Mikemirten\Component\JsonApi\Document\SingleResourceDocument;

class UserController
{
    public function postAction(SingleResourceDocument $document)
    {
        return $document;
    }
}
```
