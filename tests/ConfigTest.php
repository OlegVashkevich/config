<?php

namespace Tests;

use OlegV\Config;
use PHPUnit\Framework\TestCase;
use SensitiveParameterValue;

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
        /**
         * @var array<string, string> $secret
         */
        $secret = require $secret_path;

        $defaultConfig = [
            'secret' => $secret['very_secret'],
            'not_secret' => [
                'data1',
                'data2',
                'data3',
                'secret' => $secret['very_secret'],
                'not_secret' => [
                    'data1',
                    'data2',
                    'data3',
                    'secret' => $secret['very_secret'],
                    'not_secret' => [
                        'data1',
                        'data2',
                        'data3',
                        'secret' => $secret['very_secret'],
                    ],
                ],
            ],
        ];
        $config = new Config($secret_path, $defaultConfig);
        $defaultHidingConfig = [
            'secret' => new SensitiveParameterValue($secret['very_secret']),
            'not_secret' => [
                0 => 'data1',
                1 => 'data2',
                2 => 'data3',
                'secret' => new SensitiveParameterValue($secret['very_secret']),
                'not_secret' => [
                    0 => 'data1',
                    1 => 'data2',
                    2 => 'data3',
                    'secret' => new SensitiveParameterValue($secret['very_secret']),
                    'not_secret' => [
                        0 => 'data1',
                        1 => 'data2',
                        2 => 'data3',
                        'secret' => new SensitiveParameterValue($secret['very_secret']),
                    ],
                ],
            ],
        ];
        //check that all data hided
        $this->assertEquals($defaultHidingConfig, (array)$config);

        $secret_lvl1 = '';
        if (is_object($config['secret'])) {
            $secret_lvl1 = $config['secret']->getValue();
        }
        //check secret
        $this->assertSame($secret['very_secret'], $secret_lvl1);

        $secret_lvl2 = '';
        if (is_array($config['not_secret']) && is_object($config['not_secret']['secret'])) {
            $secret_lvl2 = $config['not_secret']['secret']->getValue();
        }
        //check secret lvl2
        $this->assertSame($secret['very_secret'], $secret_lvl2);

        $secret_lvl3 = '';
        if (is_array($config['not_secret']) && is_array($config['not_secret']['not_secret']) && is_object(
                $config['not_secret']['not_secret']['secret'],
            )) {
            $secret_lvl3 = $config['not_secret']['not_secret']['secret']->getValue();
        }
        //check secret lvl3
        $this->assertSame($secret['very_secret'], $secret_lvl3);

        $secret_lvl4 = '';
        if (is_array($config['not_secret']) && is_array($config['not_secret']['not_secret']) && is_array(
                $config['not_secret']['not_secret']['not_secret'],
            ) && is_object($config['not_secret']['not_secret']['not_secret']['secret'])) {
            $secret_lvl4 = $config['not_secret']['not_secret']['not_secret']['secret']->getValue();
        }
        //check secret lvl4
        $this->assertSame($secret['very_secret'], $secret_lvl4);

        //delete file
        unlink($secret_path);
    }

}