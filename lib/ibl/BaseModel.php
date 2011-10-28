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
                $newAttribute = $this->fromCamelCase($attribute);
                return $this->$newAttribute;
            } else {
                $newAttribute = $this->toCamelCase($attribute);
                $this->$newAttribute = $args[0];
            }
        } else {
            throw new \Exception('Call to undefined ' . get_class($this) . '::' . $name . '()'); 
        }
    }

    protected function fromCamelCase($str) {
        $str[0] = strtolower($str[0]);

        return preg_replace_callback("/([A-Z])/", function($c) {
            return "_" . strtolower($c[1]); 
        }, $str);
    }

    protected function toCamelCase($str, $capitaliseFirstChar = false)
    {
        if ($capitaliseFirstChar) {
            $str[0] = strtoupper($str[0]);
        }

        return preg_replace_callback('/_([a-z])/', function($c) {
            return strtoupper($c[1]); 
        }, $str);
    }
    
    protected function validateAttribute($name) {
        if (in_array(strtolower($name), array_keys(get_class_vars(get_class($this))))) {
            return strtolower($name); 
        } 
    }
}

