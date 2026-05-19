<?php

/**
 * Recommend: add declare(strict_types=1); at the beginning of the file
 */

namespace App\Model;

class Project
{
    /**
     * @var array
     */
    /**
     * Low Severity: 
     * 1. $_data scope should be private/protected to encapsulate object
     * 
     */
    public $_data;
    
    /**
     * Low Severity: Missing type hint for $data
     * 
     */
    public function __construct($data)
    {
        $this->_data = $data;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->_data['id'];
    }

    /**
     * Medium Severity: 
     * Seem this method is unnecessary, should replace with this:
     * class Project implements \JsonSerializable
     * ....
     * public function jsonSerialize(): array
     * {
     *   return $this->_data;
     * }
     * Then we can call "return new JsonResponse($project);" in controller
     */
    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->_data);
    }
}
