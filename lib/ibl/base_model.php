<?php

// Base data model that all our existing models will extend off of
namespace IBL;

class BaseModel
{
    protected $id;

    public function setId($id) 
    {
        if (!$this->id) {
            $this->id = $id; 
        } 
    }

    public function __call($name, $args)
    {
        if (preg_match('/^(get|set)(\w+)/', strtolower($name), $match) && $attribute = $this->validateAttribute($match[2])) {
            if ($match[1] == 'get') {
                return $this->$attribute; 
            } else {
                $this->$attribute = $args[0]; 
            }
        } else {
            throw new Exception('Call to undefined ' . get_class_name() . '::' . $name . '()'); 
        }
    }

    protected function validateAttribute($name) {
        if (in_array(strtolower($name), array_keys(get_class_vars(get_class($this))))) {
            return strtolower($name); 
        } 
    }
}

