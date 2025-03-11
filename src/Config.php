<?php

namespace OlegV;

use ArrayObject;
use SensitiveParameter;
use SensitiveParameterValue;

/**
 * Allows you to hide secret data
 *
 * @template TKey as array-key
 * @template TValue
 * @template TVal of (SensitiveParameterValue|numeric|string)
 * @extends  ArrayObject<TKey , TVal|array<TVal|array<TVal|array<TVal>>>|TValue>
 */
class Config extends ArrayObject
{
    /**
     * @var string Path to secret file which returns array<string, string>
     */
    private string $secret_path;

    /**
     * @param  string  $secret_path  Path to secret file which returns array<string, string>
     * @param  array<TKey, TValue>  $data  Configuration data is a multidimensional associative array
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
     * @param  array<TKey , TValue>  $data  Reference to configuration data array
     * @param-out  array<TKey , TValue>  $data  Reference to configuration data array
     */
    private function prepareData(array &$data): void
    {
        /**
         * @var array<string, string> $secret
         */
        $secret = require $this->secret_path;
        array_walk_recursive($data, [$this, 'hideSecret'], $secret);
    }

    /**
     * Replacing secret values with a prefix containing a key.
     *
     * @param  mixed  &$item  Reference to an element of a data array
     * @param  string  $_  Key of the current array element (not used)
     * @param  array<string, string>  $secret  Array of secret values
     */
    private function hideSecret(mixed &$item, string $_, array $secret): void
    {
        if (is_string($item)) {
            $secret_key = array_search($item, $secret, true);
            if (is_string($secret_key)) {
                /**
                 * @var SensitiveParameterValue $item
                 * @param-out  SensitiveParameterValue  $item
                 */
                $item = new SensitiveParameterValue($item);
            }
        }
    }
}