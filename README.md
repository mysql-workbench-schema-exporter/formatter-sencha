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
composer require --dev mysql-workbench-schema-exporter/sencha-exporter
```

This will install the exporter and also require [mysql-workbench-schema-exporter](https://github.com/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter).

You then can invoke the CLI script using `vendor/bin/mysql-workbench-schema-export`.

## Configuration

  * [ExtJS3 Model](/docs/sencha-extjs3.md)
  * [ExtJS4 Model](/docs/sencha-extjs4.md)

## Command Line Interface (CLI)

See documentation for [mysql-workbench-schema-exporter](https://github.com/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter#command-line-interface-cli)

## Links

  * [MySQL Workbench](http://wb.mysql.com/)
  * [Sencha - Open source FAQ](http://www.sencha.com/legal/open-source-faq/)
