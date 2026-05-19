<?php

/**
 * Recommend: add declare(strict_types=1); at the beginning of the file
 */

namespace App\Model;

class Task implements \JsonSerializable
{
    /**
     * @var array
     */
    private $_data;
    
    /**
     * Low Severity: Missing type hint for $data
     * 
     */
    public function __construct($data)
    {
        $this->_data = $data;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->_data;
    }
}
