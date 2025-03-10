# Config
Class for configuration and hiding secret data

## Features
- lightweight
- dependency-free
- 100% test coverage
- phpstan max lvl
- phpstan full strict rules

## Install
```shell
composer requier olegv/config
```
## Usage
1. Create secret file somewhere(`$path_to_secret_file`), it must return array<string, string>, for example:
    ```php
    <?php
    $secret = [
        'very_secret' => 'your_secret_key_here2',
    ];
    return $secret;
    ```
2. Create file with config array, example:
    ```php
   <?php
    $config_array = [
        'secret' => $secret['very_secret'],
        'not_secret' => [
            'data1',
            'data2',
            'data3',
            'secret' => $secret['very_secret'],
        ],
    ];
    $config = new Config($path_to_secret_file, $config_array);
    ```
3. If you need get secret data:
    ```php
    $secret_lvl1 = $config['secret']->getValue();
    $secret_lvl2 = $config['not_secret']['secret']->getValue();
    ```