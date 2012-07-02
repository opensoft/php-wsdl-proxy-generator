This package helps you generates php classes from wsdl file.

[![Build Status](https://secure.travis-ci.org/opensoft/php-wsdl-proxy-generator.png?branch=master)](http://travis-ci.org/opensoft/php-wsdl-proxy-generator)


Installation
============

If you don't have Composer yet, download it following the instructions on http://getcomposer.org/ or just run the following command:

```
curl -s http://getcomposer.org/installer | php
```

Then run

```
php composer.phar create-project opensoft/php-wsdl-proxy-generator path/to/install/directory
```


Getting started with PHP-Wsdl-Proxy-generator
=============================================

```
php app/console
```

Usage:
 *opensoft:wsdl-proxy:generate [--wsdl="..."] [--dir="..."] [--namespace="..."] [--license-path="..."]*

Options:
* --wsdl          Path to the wsdl file
* --dir           The directory where to create proxy classes
* --namespace     The namespace of proxy classes to create
* --license-path  The full path to license file. Its contents will be inserted at the beginning of each generated file

Help:
 The *opensoft:wsdl-proxy:generate* command helps you generates php classes from wsdl file.

 By default, the command interacts with the developer to tweak the generation.
 Any passed option will be used as a default value for the interaction:

```
php app/console opensoft:wsdl-proxy:generate --namespace=Acme/Wsdl
```

 Note that you can use / instead of \ for the namespace delimiter to avoid any
 problem.

 If you want to disable any user interaction, use *--no-interaction* but don't forget to pass all needed options:

```
php app/console opensoft:wsdl-proxy:generate --wsdl=simple.wsdl --dir=src --namespace=Acme/Wsdl --no-interaction
```
