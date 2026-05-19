<?php

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
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->_data);
    }
}
