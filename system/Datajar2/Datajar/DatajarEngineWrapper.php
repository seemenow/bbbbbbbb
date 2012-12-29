<?php

/**
 * @file DatajarEngineWrapper.php
 *
 * @brief Syntactic sugar for the database engines.
 * Wrapper for default datajar engine (convenient for configurable mon-db
 * systems).
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
class DatajarEngineWrapper extends DatajarEngineBase
{
    private $db;
    private static $driver = null;

    function __construct($conn = "")
    {
        if(!self::$driver) {
            throw new DatajarException(t("Unknown datajar driver."));
        } else {
            $driver = self::$driver;
            $this->db = new $driver();
            if($conn != "") {
                $this->db->init($conn);
            }
        }
    }

    /**
     * Sets a default driver.
     * @name is the driver's name.
     * @replace sets the behaviour in case of an already loaded driver. If
     *   $replace is true and a driver is already set, then this driver will be
     *   dropped and the new one loaded in its place. Otherwise (and by
     *   default), an exception will be thrown indicating a driver is already
     *   loaded.
     */
    public static function setdriver($name, $replace = FALSE)
    {
        if(self::$driver != NULL && !$replace) {
            throw new DatajarException("A datajar driver is already loaded.");
        } else {
            $drivername = "DatajarEngine".ucfirst(strtolower($name));
            // Is it loaded?
            if(!class_exists($drivername)) {
                datajar_load($name);
            }

            if(self::$driver != NULL) {

            }

            self::$driver = $drivername;
        }
    }

    /* Alright now the rest of it is only aliases functions that are redirected
       through the loaded driver.

       Note that call_user_func...() will _NOT_ work here. This is because PHP
       doesn't pass arguments to callbacks by reference, so forget it.
     */
    function create(DatajarBase $object)
    {
        return $this->db->create($object);
    }

    function save(DatajarBase $object)
    {
        return $this->db->save($object);
    }

    function delete(DatajarBase $object)
    {
        return $this->db->delete($object);
    }

    function drop($objecttype)
    {
        return $this->db->drop($objecttype);
    }

    function load(DatajarBase $object, array $cond)
    {
        return $this->db->load($object, $cond);
    }

    function select($objecttype, array $cond = null, $order = false, $desc = false)
    {
        return $this->db->select($objecttype, $cond, $order, $desc);
    }

    function run(DatajarQuery $query)
    {
        return $this->db->run($query);
    }

    function close()
    {
        return $this->db->close();
    }

    function test_backend()
    {
        return $this->db->test_backend();
    }
}

?>
