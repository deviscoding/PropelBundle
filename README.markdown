PropelBundle
============

This an unofficial implementation of [Propel 1.6](http://www.propelorm.org/) in Symfony, which has been modified to work
with PHP 7.2+ and Symfony 3.0+.

## Branching model

### Propel1 integration

The two major branches being supported are:

* The `1.7` branch contains Propel *1.6* integration for Symfony *3.x* and PHP *7.2+*.

**Note:** Only the 1.7 branch is maintained in this fork.  For newer _or_ older versions, please use the [original repo](https://github.com/propelorm/PropelBundle).

## Features

 * Generation of model classes based on an XML schema (not YAML) placed under `BundleName/Resources/*schema.xml`;
 * Insertion of SQL statements;
 * Runtime autoloading of Propel and generated classes;
 * Propel runtime initialization through the XML configuration;
 * Migrations [Propel 1.6](http://www.propelorm.org/documentation/10-migrations.html);
 * Reverse engineering from [existing database](http://www.propelorm.org/wiki/Documentation/1.6/Existing-Database);
 * Integration to the Symfony2 Profiler;
 * Load SQL, YAML and XML fixtures;
 * Create/Drop databases;
 * Integration with the Form component;
 * Integration with the Security component;
 * Propel ParamConverter can be used with Sensio Framework Extra Bundle.

For documentation, see:

    Resources/doc/

[Read the documentation](https://github.com/propelorm/PropelBundle/blob/1.5/Resources/doc/index.markdown)

For license, see:

    Resources/meta/LICENSE
