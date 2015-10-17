# Reorder plugin for CakePHP

Reorder all the values of a database field from the attached table when a change occures on that field in any rows of the table.
Reorder a database field according to changes occuring on that field.

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

### Example 1

In a playlist, the songs are listed in a specific order. If a song is deleted, inserted or modified, the play order of all other songs should be ajusted accordingly.

```
CREATE table songs(
    id int(10) unsigned NOT NULL auto_increment,
    title varchar(255) NOT NULL,
    play_order int(10) unsigned NOT NULL,
);
```

Load the behavior in your model ```SongsTable.php```:

```
$this->addBehavior('Reorder.Reorder', ['field' => 'play_order']);
```

The field must be an integer.


### Config options


```
$this->addBehavior('Reorder.Reorder', [
    // The field that is used as to keep the order, must be an integer
    'field' => 'play_order',
]);
```
