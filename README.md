# PostTypes v2.3

[![tests](https://github.com/JUVOJustin/PostTypes/actions/workflows/tests.yml/badge.svg)](https://github.com/JUVOJustin/PostTypes/actions/workflows/tests.yml) [![codecov](https://codecov.io/gh/JUVOJustin/PostTypes/branch/master/graph/badge.svg?token=SGrK2xDF46)](https://codecov.io/gh/JUVOJustin/PostTypes) [![Latest Stable Version](https://flat.badgen.net/github/release/JUVOJustin/PostTypes/stable)](https://packagist.org/packages/juvo/posttypes) [![Total Downloads](https://flat.badgen.net/packagist/dt/juvo/PostTypes)](https://packagist.org/packages/juvo/posttypes) [![License](https://flat.badgen.net/github/license/juvo/PostTypes)](https://packagist.org/packages/juvo/posttypes)

> Simple WordPress custom post types library based on the work of [**Joe Grainger**](https://jjgrainger.co.uk)

## Requirements

* PHP >=7.4
* [Composer](https://getcomposer.org/)
* [WordPress](https://wordpress.org) >=5.1

## Installation

#### Install with composer

Run the following in your terminal to install PostTypes with [Composer](https://getcomposer.org/).

```
$ composer require juvo/posttypes
```

PostTypes uses [PSR-4](https://www.php-fig.org/psr/psr-4/) autoloading and can be used with the Composer's autoloader. Below is a basic example of getting started, though your setup may be different depending on how you are using Composer.

```php
require __DIR__ . '/vendor/autoload.php';

use PostTypes\PostType;

$books = new PostType( 'book' );

$books->register();
```

See Composer's [basic usage](https://getcomposer.org/doc/01-basic-usage.md#autoloading) guide for details on working with Composer and autoloading.

## Basic Usage

Below is a basic example of setting up a simple book post type with a genre taxonomy. For more information, check out the [online documentation here](https://posttypes.jjgrainger.co.uk).

```php
// Require the Composer autoloader.
require __DIR__ . '/vendor/autoload.php';

// Import PostTypes.
use PostTypes\PostType;
use PostTypes\Taxonomy;

// Create a book post type.
$books = new PostType( 'book' );

// Attach the genre taxonomy (which is created below).
$books->taxonomy( 'genre' );

// Hide the date and author columns.
$books->columns()->hide( [ 'date', 'author' ] );

// Set the Books menu icon.
$books->icon( 'dashicons-book-alt' );

// Add custom capabilities
$books->capabilities([], ['editor', 'author']);

// Register the post type to WordPress.
$books->register();

// Create a genre taxonomy.
$genres = new Taxonomy( 'genre' );

// Set options for the taxonomy.
$genres->options( [
    'hierarchical' => false,
] );

// Register the taxonomy to WordPress.
$genres->register();
```

## Notes

* The full documentation can be found online at [posttypes.jjgrainger.co.uk](https://posttypes.jjgrainger.co.uk)
* The class has no methods for making custom fields for post types, use [Advanced Custom Fields](https://advancedcustomfields.com)
* The book's example used in the README.md can be found in [examples/books.php](examples/books.php)
* Licensed under the [MIT License](https://github.com/jjgrainger/PostTypes/blob/master/LICENSE)
* Maintained under the [Semantic Versioning Guide](https://semver.org)

## Original Author

**Joe Grainger**

* [https://jjgrainger.co.uk](https://jjgrainger.co.uk)
* [https://twitter.com/jjgrainger](https://twitter.com/jjgrainger)
