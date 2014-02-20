RdnRouter
=========

The **RdnRouter** ZF2 module provides a very simple debug utility that displays information about all your HTTP routes.

## Installation

1. Use `composer` to require the `radnan/rdn-router` package:

   ~~~bash
   $ composer require radnan/rdn-router:1.*
   ~~~

2. Activate the module by including it in your `application.config.php` file:

   ~~~php
   <?php

   return array(
       'modules' => array(
           'RdnRouter',
           // ...
       ),
   );
   ~~~

## Usage

Define all your routes using the `router.routes` configuration option. Then, simply run the following command from your project root:

~~~bash
$ vendor/bin/console router:debug
~~~

You can also view a bit more detailed information about a single route by using the full route name as an argument:

~~~bash
$ vendor/bin/console router:debug foo/bar
~~~
