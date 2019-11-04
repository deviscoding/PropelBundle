PropelBundle
============

This an unofficial implementation of [Propel 1.6](http://www.propelorm.org/) in Symfony, which has been modified to work
with PHP 7.2, PHP 7.3, and Symfony 3.x.


## Installation ##

The recommended way to install this bundle is to rely on [Composer](http://getcomposer.org):

``` javascript
{
    "require": {
        // ...
        "deviscoding/propel1-bundle": "^1.7"
    }
}
```

The second step is to register this bundle in the `AppKernel` class:

``` php
public function registerBundles()
{
    $bundles = array(
        // ...
        new Propel\Bundle\PropelBundle\PropelBundle(),
    );

    // ...
}
```

You are almost ready, the next steps are:

* to [configure the bundle](configuration.markdown);
* to [configure Propel](propel_configuration.markdown);
* to [write an XML schema](schema.markdown).

Now, you can build your model classes, and SQL by running the following command:

    > php app/console propel:build [--classes] [--sql] [--insert-sql] [--connection[=""]]

To insert SQL statements, use the `propel:sql:insert` command:

    > php app/console propel:sql:insert [--force] [--connection[=""]]

Note that the `--force` option is needed to actually execute the SQL statements.

Congratulation! You're done, just use the Model classes as any other class in Symfony2:

``` php
<?php

class HelloController extends Controller
{
    public function indexAction($name)
    {
        $author = new \Acme\DemoBundle\Model\Author();
        $author->setFirstName($name);
        $author->save();

        return $this->render('AcmeDemoBundle:Hello:index.html.twig', array(
            'name' => $name, 'author' => $author)
        );
    }
}
```

Now you can read more about:

* [The Commands](commands.markdown);
* [The Fixtures](fixtures.markdown);
* [The PropelParamConverter](param_converter.markdown);
* [The UniqueObjectValidator](unique_object_validator.markdown).
* [The ModelTranslation](model_translation.markdown).


## Bundle Inheritance ##

The `PropelBundle` makes use of the bundle inheritance. Currently only schema inheritance is provided.

### Schema Inheritance ###

You can override the defined schema of a bundle from within its child bundle.
To make use of the inheritance you only need to drop a schema file in the `Resources/config` folder of the child bundle.

Each file can be overridden without interfering with other schema files.
If you want to remove parts of a schema, you only need to add an empty schema file.
