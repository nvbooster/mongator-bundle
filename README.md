![Mongator Symfony Bundle](http://s23.postimg.org/u2gmrpu5n/mongator_bundle.png)

#Mongator Bundle [![Build Status](https://travis-ci.org/dario1985/mongator-bundle.png)](https://travis-ci.org/dario1985/mongator-bundle) [![Coverage Status](https://coveralls.io/repos/dario1985/mongator-bundle/badge.png)](https://coveralls.io/r/dario1985/mongator-bundle)

Bundle to use Mongator ODM with Symfony2 (forked from MandangoBundle).

## Getting started

### Installation

To install mongator-bundle with Composer, just add the following to your composer.json file:

```js
// composer.json
{
    "require": {
        "mongator/mongator-bundle": "1.1.*"
    }
}
```

Then update the dependency

```sh
$ php composer.phar update mongator/mongator-bundle
```

Or you can do this in one command:

```sh
$ php composer.phar require mongator/mongator-bundle:1.1.*
```

### Enable the bundle

You need to register the bundle in AppKernel:

```php

    // app/AppKernel.php

    class AppKernel extends Kernel
    {
        // ...

        public function registerBundles()
        {
            $bundles = array(
                // ...,
                new Mongator\MongatorBundle\MongatorBundle()
            );

            // ...
        }
    }
```
### Configure Mongator

Add Mongator to your configuration:

```yml
# app/config/config.yml
mongator:
    default_connection: local
    connections:
        local:
            server:   mongodb://localhost:27017
            database: symfony2_local_%kernel.environment%
```

Activate the profiler in the developing environment:

```yml
# app/config/config_dev.yml
mongator:
    logging: true
```

## Documentation

See the documentation in [http://mandango.org/doc/](http://mandango.org/doc/)
