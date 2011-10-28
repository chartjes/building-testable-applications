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
        if (preg_match('/^(get|set)(\w+)/', $name, $match) && $attribute = $this->validateAttribute($match[2])) {
            if ($match[1] == 'get') {
                $newAttribute = $this->inflect($attribute);
                return $this->$newAttribute;
            } else {
                $newAttribute = $this->inflect($attribute);
                $this->$newAttribute = $args[0];
            }
        } else {
            throw new \Exception('Call to undefined ' . get_class($this) . '::' . $name . '()'); 
        }
    }

    protected function inflect($str) {
        $str[0] = strtolower($str[0]);

        return preg_replace_callback("/([A-Z])/", function($c) {
            return "_" . strtolower($c[1]); 
        }, $str);
    }
    
    protected function validateAttribute($name) {
        $fieldName = $this->inflect($name);

        if (in_array(strtolower($fieldName), array_keys(get_class_vars(get_class($this))))) {
            return $fieldName; 
        } 
    }
}

