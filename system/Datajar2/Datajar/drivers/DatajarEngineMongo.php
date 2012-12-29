<?php

/**
 * @file DatajarEngineMongo.php
 *
 * @brief Implements a datajar driver for MongoDB.
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
class DatajarEngineMongo extends DatajarEngineBase
{
    protected $conn;
    protected $db;

    public function __construct($conn = "")
    {
        if($conn != "") {
            $this->init($conn);
        }
    }

    public function init($conn_string)
    {
        $conn = $this->parse_conn_string($conn_string);
        $db_conn = "";
        if($conn['host']) {
            if($conn['username']) {
                $db_conn = sprintf('mongodb://%s:%s@%s', $conn['username'],
                                   $conn['password'], $conn['host']);
            } else {
                $db_conn = sprintf('mongodb://%s', $conn['host']);
            }
        } else {
            $db_conn = sprintf('mongodb://localhost');
        }
        $this->conn = new Mongo($db_conn);
        $this->db = new MongoDB($this->conn, $conn['database']);
    }

    public function create(DatajarBase $object)
    {
        // Nothing, mongo doesn't need the schema to be initialized (cool eh!)
    }

    /**
     * Converts a Datajar object in to an array.
     */
    protected function serialize_object($object)
    {
        $serial = array();

        $this->require_datajar($object);

        $props = $object->prototype();
        foreach($props as $prop) {
            $serial[$prop['name']] = $prop['val']->getVal();
        }

        return $serial;
    }

    protected function parse_cond(array $cond) {
        // TODO. Works right away for very simple conditions.
        return $cond;
    }

    public function save(DatajarBase $object)
    {
        $data = $this->serialize_object($object);
        if($data) {
            $col = new MongoCollection($this->db, $this->obj_name($object));
            $col->insert($data, array('fsync' => true));
            $object->setid((string)$data['_id']);
            return true;
        } else {
            return false;
        }
    }

    public static function escape($data)
    {
        return $data;
    }

    public function delete(DatajarBase $object)
    {
        $col = new MongoCollection($this->db, $this->obj_name($object));
        $col->remove(array('_id' => new MongoId($object->id)), array('fsync' => true));
    }

    public function load(DatajarBase $object, array $cond)
    {
        $col = new MongoCollection($this->db, $this->obj_name($object));
        $data = $col->findOne($cond);

        if(!$data) {
            return false;
        }

        $props = $object->prototype();
        foreach($props as $prop) {
            if(isset($data[$prop['name']])) {
                $object->__set($prop['name'], $data[$prop['name']]);
            }
        }

        return true;
    }

    public function select($objecttype, array $cond = NULL, $order = false,
                           $desc = false, array $limit = NULL)
    {
        $col = new MongoCollection($this->db, $objecttype);
        $data = $col->find($cond);

        if($order) {
            $val = $desc? -1 : 1;
            $data = $data->sort(array($order => $val));
        }

        if(is_array($limit) && count($limit) == 2) {
            $data = $data->limit($limit[1])->skip($limit[0]);
        }

        if(!$data || $data->count() < 1) {
            return false;
        }

        $objs = array();
        foreach($data as $record) {
            $object = new $objecttype();
            $props = $object->prototype();
            foreach($props as $prop) {
                if(isset($record[$prop['name']])) {
                    $object->__set($prop['name'], $record[$prop['name']]);
                }
            }
            $objs[] = $object;
        }

        return $objs;
    }

    public function run(DatajarQuery $query)
    {
        return $this->select($query->get_object(),
                             $query->get_cond(),
                             $query->get_orderby(),
                             $query->get_desc(),
                             $query->get_limit());
    }

    public function drop($object)
    {
        $col = new MongoCollection($this->db, $this->obj_name($object));
        $col->drop();
    }

    public function close()
    {
        // Nothing here.
    }

    public static function test_backend()
    {
        return class_exists('Mongo');
    }
}


?>
