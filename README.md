[![Build Status](https://travis-ci.org/gintonicweb/reorder.svg)](https://travis-ci.org/gintonicweb/reorder)
[![Coverage Status](https://coveralls.io/repos/gintonicweb/reorder/badge.svg?branch=master&service=github)](https://coveralls.io/github/gintonicweb/reorder?branch=master)
[![Packagist](https://img.shields.io/packagist/dt/gintonicweb/reorder.svg)]()
[![Software License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

# Reorder plugin for CakePHP

Reorder a database field according to changes occuring on that field. A change can happen from a deleted or inserted row. It can also come from the modification of the chosen field of any existing row.

## Installation

Using [composer](http://getcomposer.org).

```
composer require gintonicweb/reorder:dev-master
```

Load the plugin in ```bootstrap.php``` like:

```
Plugin::load('Reorder');
```

## Usage

### Config options


```
$this->addBehavior('Reorder.Reorder', [
    // The fields that are used to keep the order, must be an integer fields
    'field1' => [ 'param' => null ],
    'field2' => [ 'param' => null ],
]);
```
Currently no more options are supported, additional parameters can be left empty.

### Example 1

In a playlist, the songs are listed in a specific order. If a song is deleted, inserted or modified, the play order of all other songs should be ajusted accordingly.

```
CREATE table songs(
    id int(10) unsigned NOT NULL auto_increment,
    title varchar(255) NOT NULL,
    play_order int(10) unsigned NOT NULL,
    play_order_artist int(10) unsigned NOT NULL,
);
```

Load the behavior in your model ```SongsTable.php``` (the field must be an integer):

```
$this->addBehavior('Reorder.Reorder', [
    'play_order' => [],
    'play_order_artist' => [],
]);
```

Suppose we have the table filled like this:

| id        | title           | play_order  | play_order_artist  |
| --- |:-------------:| :---:| :---:|
| 1      | Best Song | 1 | 3 |
| 2      | Sad Song      |   2 | 2 |
| 3      | Popular Song      |    3 | 1 |

and that the *play_order* of the *Best Song* is **modified** from 1 to 3, the table will be re-ordered as follow:

| id        | title           | play_order  | play_order_artist  |
| --- |:-------------:| :---:| :---:|
| 1      | Best Song | 3 | 3 |
| 2      | Sad Song      |   1 | 2 |
| 3      | Popular Song      |    2 | 1 |

If *New Song* is **inserted** with *play_order* set to 1 and *play_order_artist* set to 1, the table will now look like this:

| id        | title           | play_order  | play_order_artist  |
| --- |:-------------:| :---:| :---:|
| 1      | Best Song | 4 | 4 |
| 2      | Sad Song      |   2 | 3 |
| 3      | Popular Song      |    3 | 2 |
| 4      | New Song      |    1 | 1 |

Lastly, if the *Popular Song* is not so popular anymore and is **deleted** from the list, the table will end up like this:

| id        | title           | play_order  | play_order_artist  |
| --- |:-------------:| :---:| :---:|
| 1      | Best Song | 3 | 3 |
| 2      | Sad Song      |   2 | 2 |
| 4      | New Song      |    1 | 1 |

