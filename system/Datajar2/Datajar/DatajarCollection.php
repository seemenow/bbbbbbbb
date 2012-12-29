<?php
/**
 * @file StorableCollection.php
 *
 * @brief A object that contains DatajarBase-derived objects. It is mostly used
 * to contain children of DatajarBase objects within themselves.
 *
 * This is also a class factory that can instanciate new objects bound to the
 * original.
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

class DatajarCollection
{
    protected $objects = array();
    protected $mother;

    public function __construct(&$mother)
    {
        $this->mother = $mother;
    }

    public function load($id)
    {
        $children = DatajarSchema::get_relations(get_class($this->mother));

        foreach($children as $relation) {
            $this->objects[$rel['class']] =
                $child_class::objects(array($rel['var'] => $id));
        }
    }

    public function add(&$obj)
    {
        $children = DatajarSchema::get_child_classes(get_class($this->mother));

        if(in_array(get_class($obj), $children)) {
            $this->objects[get_class($obj)][] = &$obj;
        }

        /*$children = DatajarSchema::get_relations(get_class($this->mother));

        $var = "";
        foreach($children as $rel) {
            if($rel['class'] == get_class($obj)) {
                $var = $rel['var'];
                break;
            }
        }

        if($var != "") {
            $obj->$var = $this->mother->id;
            }*/

        return $obj;
    }

    protected function walk_objects($func, array $args)
    {
        $outp = array();
        foreach($this->objects as $objects) {
            foreach($objects as $object) {
                $outp[] = call_user_func_array(array($object, $func), $args);
            }
        }

        return $outp;
    }

    public function save()
    {
        return $this->walk_objects('save', func_get_args());
    }

    public function delete()
    {
        return $this->walk_objects('delete', func_get_args());
    }

    public function drop()
    {
        return $this->walk_objects('drop', func_get_args());
    }
}

?>