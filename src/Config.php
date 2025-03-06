<?php

namespace OlegV;

use ArrayObject;
use SensitiveParameter;

/**
 * Allows you to hide secret data
 *
 * @template TKey of (int|string)
 * @template TValue
 * @extends  ArrayObject<TKey , TValue>
 */
class Config extends ArrayObject
{
    /**
     * @var string Path to secret file which returns array<string, string>
     */
    private string $secret_path;

    /**
     * @param  string  $secret_path  Path to secret file which returns array<string, string>
     * @param  array<TKey , TValue>  $data  Configuration data is a multidimensional associative array
     */
    public function __construct(
        #[SensitiveParameter] string $secret_path,
        #[SensitiveParameter] array $data = [],
    ) {
        $this->secret_path = $secret_path;
        $this->prepareData($data);
        parent::__construct($data);
    }

    /**
     * Preparing configuration data by replacing secret values with a keyed prefix
     *
     * @param  array<TKey , TValue> &$data  Reference to configuration data array
     */
    private function prepareData(array &$data): void
    {
        $secret = require $this->secret_path;
        array_walk_recursive($data, [$this, 'hideSecret'], $secret);
    }

    /**
     * Getting a secret value by key.
     *
     * @param  string  $key  Key to get secret value
     * @return string|null The value of the secret or null if the value is not found
     */
    public function getSecret(string $key): string|null
    {
        $secret = require $this->secret_path;
        $arKey = explode($this->getSecretPrefix(), $key);

        $value = null;
        if (!empty($arKey[1]) && !empty($secret[$arKey[1]])) {
            $value = $secret[$arKey[1]];
        }
        return $value;
    }

    /**
     * Getting prefix for secret values.
     *
     * @return non-empty-string Prefix for secret values
     */
    public function getSecretPrefix(): string
    {
        return 'secret#';
    }

    /**
     * Replacing secret values with a prefix containing a key.
     *
     * @param  mixed &$item  Reference to an element of a data array
     * @param  string  $_  Key of the current array element (not used)
     * @param  array<string, string>  $secret  Array of secret values
     */
    private function hideSecret(mixed &$item, string $_, array $secret): void
    {
        if (is_string($item) && $secret_key = array_search($item, $secret)) {
            $item = $this->getSecretPrefix().$secret_key;
        }
    }
    //check phpstan workflow
}