<?php

/**
 * @file StorableBase.php
 *
 * @brief Basic implementation of a self-generating storable object. This class
 * is not made to be used straight-away but to be extended.
 *
 * Copyright © 2012 Guillaume Pasquet
 *
 * This file is part of Datajar.
 *
 * Datajar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * Datajar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Datajar.  If not, see <http://www.gnu.org/licenses/>.
 */

class DatajarBase
{
    protected $id = false;
    public $children;
    protected static $db = null;

    /**
     * Constructor.
     */
    public function __construct(array $init = null)
    {
        $this->type_init();

        $this->children = new DatajarCollection($this);

        if(is_array($init)) {
            $this->populate($init);
        }
    }

    /**
     * Defines a common database connection for Storable objects.
     */
    public static function bind($db)
    {
        if(DatajarEngineBase::does_extend($db, "DatajarEngineBase")) {
            self::$db = $db;
        } else {
            throw new DatajarException(sprintf("Cannot bind non-DatajarEngine object."));
        }
    }

    protected static function is_bound()
    {
        return (self::$db !== null);
    }

    protected static function require_bound()
    {
        if(!self::is_bound()) {
            throw new DatajarException(sprintf("Object not bound to DatajarEngine."));
            return false;
        }

        return true;
    }

    /**
     * Sets the object's value based on the given array. The array's keys are
     * used as member variables's names and the associated values are set to
     * these.
     *
     * This only works with Storable member variables. Attempting to set any
     * other variable will result in a DatajarException.
     */
    public function populate(array $vals)
    {
        foreach($vals as $varname => $varval) {
            if(isset($this->$varname) && $this->is_typed($this->$varname)) {
                $this->$varname->setval($varval);
            }
            else if(!isset($this->$varname)) {
                throw new DatajarException(sprintf("Unknown property %s", $varname));
            }
            else {
                throw new DatajarException(sprintf("Attempting to access private member `%s' of class `%s'.", $varname, get_class($this)));
            }
        }
    }

    public function __get($name)
    {
        if(isset($this->$name) && $this->is_typed($this->$name)) {
            return $this->$name->getval();
        }
        else if($name == 'id') {
            return $this->id;
        }
        else {
            throw new DatajarException(sprintf("Attempting to access private member `%s' of class `%s'.", $name, get_class($this)));
        }
    }

    public function __set($name, $value)
    {
        if(isset($this->$name) && $this->is_typed($this->$name)) {
            return $this->$name->setval($value);
        } else {
            debug_print_backtrace();
            throw new DatajarException(sprintf("Attempting to access private member `%s' of class `%s'.", $name, get_class($this)));
        }
    }

    /**
     * Initialize types in here.
     */
    protected function type_init()
    {
    }

    /**
     * Determines if the given object extends DatajarTypeBase.
     */
    protected function is_typed($object)
    {
        return DatajarEngineBase::does_extend($object, "DatajarTypeBase");
    }

    /**
     * Is this a linked object?
     */
    protected function is_child($object)
    {
        return DatajarEngineBase::does_extend($object, "DatajarTypeForeignKey");
    }

    /**
     * Handy function, in particular for debugging. Overloading it is a good idea.
     */
    public function tostring()
    {
        $buffer = "(".get_class($this)." id: " . (($this->id != false)? $this->id : 'New') . ") {\n";
        foreach($this as $propname => $propval) {
            if($this->is_typed($propval)) {
                $buffer.=
                    "    [" . $propname . ": '" . $propval->getval() . "'] \n";
            }
        }

        return $buffer . "}\n";
    }

    /**
     * Helper to assign a foreign key to a member variable.
     */
    protected function foreignkey($var, $class)
    {
        $this->$var = DatajarType::foreignkey(get_class($this), $var, $class);
    }

    public function cascade($action)
    {
        $stmt = array();

        // Are there extra args?
        $args = array();
        if(count(func_get_args()) > 2) {
            $args = array_slice(func_get_args(), 2);
        }

        foreach($this as $propname => $propval) {
            // Must be a storable property.
            if($this->is_child($propval)) {
                $stmt[] = array(
                    'name' => $propname,
                    'val' => $propval->apply($action, $args),
                    );
            }
        }

        return $stmt;
    }

    /**
     * Returns the object's prototype.
     */
    public function prototype()
    {
        $proto = array();

        // Are there extra args?
        $args = array();
        if(count(func_get_args()) > 2) {
            $args = array_slice(func_get_args(), 2);
        }

        foreach($this as $propname => $prop) {
            // Must be a storable property.
            if($this->is_typed($prop)) {
                $proto[] = array(
                    'name' => $propname,
                    'val'  => $prop,
                    );
            }
        }

        return $proto;
    }

    /**
     * Sets the object's ID.
     */
    public function setid($id)
    {
        if($this->id !== false) {
            throw new DatajarException(sprintf("Attempting to set the id of an existing object."));
        } else {
            $this->id = $id;
            return $this->id;
        }
    }

    /**
     * Unsets the object's ID.
     */
    public function clearid()
    {
        $this->id = false;
    }

	/**
     * Convenience helper that wraps a reflection class call.
     */
    public function objname()
    {
        $refl = new ReflectionClass($this);
        return $refl->getName();
    }

    /* ******* Convenience wrapper **********/
    public function create()
    {
        $this->require_bound();
        return self::$db->create($this);
    }

    public function save()
    {
        $this->require_bound();
        return self::$db->save($this);
    }

    public function delete()
    {
        $this->require_bound();
        return self::$db->delete($this);
    }

    public function drop()
    {
        $this->require_bound();
        return self::$db->drop($this);
    }

    public function load($cond)
    {
        $this->require_bound();
        return self::$db->load($this, $cond);
    }

    public static function select(array $cond = NULL, $order = false,
                                  $desc = false, array $limit = NULL)
    {
        self::require_bound();
        return self::$db->select(get_called_class(), $cond, $order, $desc, $limit);
    }

    public static function query()
    {
        return new DatajarQuery(get_called_class());
    }

    public static function run_query($query)
    {
        self::require_bound();
        return self::$db->run($query);
    }
}

?>
