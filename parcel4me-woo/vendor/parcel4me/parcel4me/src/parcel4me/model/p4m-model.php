<?php

namespace P4M\Model;

/* 

    P4M Model simply provides a standard getter and setter, 
    and the "removeNullProperties()" method, to make it easier
    to work with the PHP objects used for the P4M models 

*/

class P4mModel
{

    public function __get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value) {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
        return $this;
    }


    public function removeNullProperties() {

        foreach ($this as $key => $val) {
            // keep array and object properties exactly as they are (ie. this method is not currently recursive)
            if (gettype($val)!='object' && gettype($val)!='array') {
                // else drop NULL values
                if (is_null($val)) unset($this->$key);
            }
        }

    }

}

?>