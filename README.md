![Build Status](https://github.com/mysql-workbench-schema-exporter/sencha-exporter/actions/workflows/continuous-integration.yml/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/mysql-workbench-schema-exporter/sencha-exporter/v/stable.svg)](https://packagist.org/packages/mysql-workbench-schema-exporter/sencha-exporter)
[![Total Downloads](https://poser.pugx.org/mysql-workbench-schema-exporter/sencha-exporter/downloads.svg)](https://packagist.org/packages/mysql-workbench-schema-exporter/sencha-exporter) 
[![License](https://poser.pugx.org/mysql-workbench-schema-exporter/sencha-exporter/license.svg)](https://packagist.org/packages/mysql-workbench-schema-exporter/sencha-exporter)

# README

This is an exporter to convert [MySQL Workbench](http://www.mysql.com/products/workbench/) Models (\*.mwb) to a Sencha ExtJS3 and ExtJS4 Schema.

## Prerequisites

  * PHP 7.2+
  * Composer to install the dependencies

## Installation

```
php composer.phar require --dev mysql-workbench-schema-exporter/sencha-exporter
```

This will install the exporter and also require [mysql-workbench-schema-exporter](https://github.com/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter).

You then can invoke the CLI script using `vendor/bin/mysql-workbench-schema-export`.

## Formatter Setup Options

Additionally to the [common options](https://github.com/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter#configuring-mysql-workbench-schema-exporter) of mysql-workbench-schema-exporter these options are supported:

### ExtJS3 Model

#### Setup Options

  * `classPrefix`

    Class prefix for generated object.

    Default is `SysX.App`.

  * `parentClass`

    Ancestor object, the class to extend for generated javascript object.

    Default is `SysX.Ui.App`.

### ExtJS4 Model

#### Setup Options

  * `classPrefix`

    Class prefix for generated object.

    Default is `App.model`.

  * `parentClass`

    Ancestor object, the class to extend for generated javascript object.

    Default is `Ext.data.Model`.

  * `generateValidation`

    Generate columns validation.

    Default is `true`.

  * `generateProxy`

    Generate ajax proxy.

    Default is `true`.

  * `addIdProperty`

    Add the primary key of a table as the model's idProperty.
    ([Reference](http://docs.sencha.com/extjs/4.2.3/#!/api/Ext.data.Model-cfg-idProperty))

    Default is `false`.

## Command Line Interface (CLI)

See documentation for [mysql-workbench-schema-exporter](https://github.com/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter#command-line-interface-cli)

## Links

  * [MySQL Workbench](http://wb.mysql.com/)
  * [Sencha - Open source FAQ](http://www.sencha.com/legal/open-source-faq/)
