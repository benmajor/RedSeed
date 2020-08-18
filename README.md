# RedSeed

RedSeed is a database seeder for use with the popular (and amazing) RedBean ORM for PHP. 

### What is database seeding?

Database seeding is the initial seeding of a database with data. Seeding a database is a process in which an initial set of data is provided to a database when it is being installed. It is especially useful when we want to populate the database with data we want to develop in future. This is often an automated process that is executed upon the initial setup of an application. The data can be dummy data or necessary data such as an initial administrator account. 

### Incentive

Popular PHP ORMs support database seeding, and I found myself increasingly needing to do it while working with RedBean. Manually creating data for use inside an application which uses RedBean can be tedious, and often involves loops and other verbose code techniques to get the job done. As such, I decided that it would be worthwhile to build upon the great plugin architecture of RedBean, and following a discussion with the RB creators and devs, we agreed it should be started. It is my hope that this repo (and in turn the RedSeed project) will grow with time, and I would certainly recommend any feedback or suggestions from users. Please use Github Issues to file suggestions, comments or bug reports.

### Installation

The easiest way to install RedSeed is to use [Composer](https://www.getcomposer.com):

```bash
$ composer require benmajor/redseed
```

Once you have required the project via Composer, you must ensure that you include the autoload file inside of your project:

```php
require 'vendor/autoload.php';
```

Alternatively, you can download the contents of the `src/` directory, and include all of the files inside of your project. **We do not recommend installing this way!**

### 1. Usage:

RedSeed adds a new method to the `R` class of RedBean called `seed`. Below is an example of seeding 10 `user` beans with several fields defined:

```php
<?php
use RedBeanPHP\R;

require 'vendor/autoload.php';

$users = R::seed('user', 10, [
  'forename' => 'word(3,10)',
  'surname' => 'word(5,15)',
  'email' => 'email()'
]);
```

This will create 10 `user` beans with the fields `forename`, `surname` and `email`, and returns an array containing the generated beans. RedSeed automatically adds a new field to the schema named `_seeded`, which is used when unseeding a table. Below is an example of how to unseed the `user` table:

```php
R::unseed('user');
```

Unseeding a table will delete any records that were created using the `seed` function, by analysing records whose `_seeded` property is set to `1`. To avoid any data loss, do not modify the value of `_seeded` against other records in the table. Calling `unseed` will cause the `_seeded` column to be dropped from the schema.

### 2. Functions:

RedSeed includes a number of handy predefined functions that should be passed as the array value in the last argument  of the `seed` function (see example above). In addition, it is also possible to pass in a lambda function that returns some value:

```php
# Generate 10 user beans, all with the forename property of Ben:
R::seed('user', 10, [
  'forename' => function() { return 'Ben'; } 
]);
```

**If a lambda function returns an array, its return value will be used for the bean's `ownXList` property!** For example:

```php
R::seed('user', 10, [
  'forename' => 'word(5,10)',
  'surname' => 'word(10,15)',
  'email' => 'email()',
  'login' => function() {
      return R::seed('login', 2, [
        'ipaddress' => 'ipaddress()',
        'date' => 'datetime()'
      ]);
  }
]);
```

In the example above, 10 `user` beans will be created, each having two `login` objects assigned to its `ownLoginList` property. 

Below is a table which lists the functions available to fields in RedSeed:

| Function name | Description                                                  | Arguments                    | Example          |
| ------------- | ------------------------------------------------------------ | ---------------------------- | ---------------- |
| `string`      | Returns a random string (all lower case) of variable length (between `min` and `max` chars).<br />**Return:** `aytwr`. | `min = int`<br />`max = int` | `string(3,10)`   |
| `word`        | Returns a random string with the first character in upper case of variable length (between `min` and `max` chars).<br />**Return:** `Lotfsa`. | `min = int`<br />`max = int` | `word(5,10)`     |
| `integer`     | Generates a random integer between `min` and `max`<br />**Return:** `53`. | `min = int`<br />`max = int` | `integer(1,100)` |
| `time`        | Returns the current time in ISO 8601 format.<br />**Return:** `10:16:53`. | *None*                       | `time()`         |
| `date`        | Returns the current date in ISO 8601 format.<br />**Return:** `2020-08-16`. | *None*                       | `date()`         |
| `datetime`    | Returns the current date-time in ISO 8601 format.<br />**Return:** `2020-08-16 10:16:53`. | *None*                       | `datetime()`     |
| `email`       | Returns a random, verifiable email address.<br />**Return:** `htwvf@gmail.com`. | *None*                       | `email()`        |
| `ipaddress`   | Generates a random, verifiable IPv4 address.<br />**Return:** `174.68.10.79`. | *None*                       | `ipaddress()`    |

### 3. Contribute:

I would welcome any assistance and feedback in managing and developing RedSeed. There is an issue over on the RedBean GitHub repository regarding its discussion (https://github.com/gabordemooij/redbean/issues/837), but please keep use the RedSeed repository to post issues and comments about the plugin. 

### 4. Roadmap:

I plan to add more methods and functionality to RedSeed in the coming weeks, at this stage, the project is a proof-of-concept. The following is a *very* rough roadmap of things I plan to implement:

- `tag()` function, to easily generate tags for a bean
- `password()` a method for generating passwords using RedSeed (for now, this functionality should be achieved using lambdas).

### License:

Copyright 2020 Ben Major.

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.