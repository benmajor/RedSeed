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