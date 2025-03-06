<?php

namespace Tests;

use OlegV\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testDefaultConfigAndGetSecret(): void
    {
        //let this will secret array
        //create secret file for test witch return array
        $php_secret_file_content = <<<PHP_SECRET
            <?php
            \$secret = [
                'very_secret' => 'your_secret_key_here2',
            ];
            return \$secret;
            PHP_SECRET;
        $secret_path = __DIR__.DIRECTORY_SEPARATOR.'secret.php';
        file_put_contents($secret_path, $php_secret_file_content);

        //get array
        $secret = require $secret_path;

        $defaultConfig = [
            'secret' => $secret['very_secret'],
            'not_secret' => [
                'data1',
                'data2',
                'data3',
                'secret' => $secret['very_secret'],
            ],
        ];
        $config = new Config($secret_path, $defaultConfig);

        $defaultHidingConfig = [
            'secret' => 'secret#very_secret',
            'not_secret' => [
                0 => 'data1',
                1 => 'data2',
                2 => 'data3',
                'secret' => 'secret#very_secret',
            ],
        ];
        //check that all data hided
        $this->assertSame($defaultHidingConfig, (array)$config);

        $secret_lvl1 = '';
        if (is_string($config['secret'])) {
            $secret_lvl1 = $config->getSecret($config['secret']);
        }
        //check secret
        $this->assertSame($secret['very_secret'], $secret_lvl1);

        //delete file
        unlink($secret_path);
    }

}