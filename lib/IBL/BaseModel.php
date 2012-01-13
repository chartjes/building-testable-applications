<?php

// Base data model that all our existing models will extend off of
namespace IBL;

class BaseModel
{
    protected $_id;

    public function setId($id) 
    {
        // Never allow the ID for a model to be overwritten
        if (!$this->_id) {
            $this->_id = $id; 
        } 
    }

    public function __call($name, $args)
    {
        if (preg_match('/^(get|set)(\w+)/', $name, $match)) {
            $attribute = $this->validateAttribute($match[2]);

            if ($match[1] == 'get') {
                return $this->{$attribute};
            } else {
                $this->{$attribute} = $args[0];
            }
        } else {
            throw new \Exception(
                'Call to undefined ' . 
                __CLASS__ . 
                '::' . 
                $name . 
                '()'
            ); 
        }
    }
    
    protected function validateAttribute($name) 
    {
        // Convert first character of the string to lowercase 
        $field = '_' . $name;
        $field[1] = strtolower($field[1]); 

        // We don't use __CLASS__ here because there are scope problems
        $currentClass = get_class($this);

        if (in_array($field, array_keys(get_class_vars($currentClass)))) {
            return $field; 
        }

        return false;
    }
}

