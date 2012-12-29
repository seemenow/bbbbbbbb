<?php

/**
 * @file DatajarEngineBase.php
 *
 * @brief Basic implementation and utilities to create datajar drivers.
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

abstract class DatajarEngineBase
{
    /**
     * Creates the datajar object (table) associated to the object.
	 * @param $object is an object that extends DatajarBase.
	 * @return TRUE on success, FALSE on error.
     */
    public function create(DatajarBase $object)
	{
		$q = new DatajarQuery();
		$q->create($object);
		return $this->run($q);
	}

    /**
     * Saves the object into its datajar.
	 * @param $object is a DatajarBase object to be saved.
	 * @return TRUE on success, FALSE on error.
     */
    public function save(DatajarBase $object)
	{
		$q = new DatajarQuery();
		$q->save($object);
		return $this->run($q);
	}

    /**
     * Deletes the object from its datajar.
	 * @param $object is a DatajarBase object.
	 * @return TRUE on success, FALSE on error.
     */
	public function delete(DatajarBase $object)
	{
		$q = new DatajarQuery();
		$q->delete($object);
		return $this->run($q);
	}

    /**
     * Deletes the datajar associated to the object.
	 * @param $object is either a DatajarBase object to be dropped or
	 * a DatajarBase-extending classname.
     */
	public function drop($objecttype)
	{
		$q = new DatajarQuery();
		$q->drop($objecttype);
		return $this->run($q);
	}

    /**
     * Loads up the data corresponding to the object in the datajar.
	 * @param $object is a DatajarBase object.
	 * @param $cond is a conditional array.
	 * @return TRUE on success, FALSE on error.
     */
    abstract public function load(DatajarBase $object, array $cond);

    /**
     * Loads objects from the Datajar back-end.
	 * @param $objecttype is the classname of the object to load.
	 * @param $cond is a conditional array.
	 * @param $order is an optional order-by parameter; results will
	 * be ordered by the given field name, or not ordered if false is
	 * given.
	 * @param $desc is optional, TRUE will list objects descending.
	 * @return an array of $objecttype objects, FALSE on error.
     */
    public function select($objecttype, array $cond = null, $order = false, $desc = false)
	{
		$q = new DatajarQuery();
		$q->select($objecttype)->where($cond);
		if($order) {
			$q->orderby($order, $desc);
		}

		return $this->run($q);
	}

    /**
     * Runs the provided DatajarQuery query object.
	 * @param $query is a DatajarQuery object.
	 * @return Mixed.
     */
    abstract public function run(DatajarQuery $query);

    /**
     * Escapes a string to secure it.
	 * @param $data is an unescaped string.
	 * @return the string $data escaped.
     */
    public static function escape($data)
    {
        return $data;
    }
	
    /**
     * Closes the connection.
     */
    abstract public function close();

    /**
     * Logs or prints the query depending on the status of the constant
     * DB_DEBUG.
     */
    protected function log($query)
    {
        if(defined('DB_DEBUG')) {
            $logstr = date("Y-m-d H:i:s :: ") . $query . "\n";
            if(strtolower(DB_DEBUG) == 'on') {
                echo $logstr;
            }
            else if(defined('DB_LOGFILE')) {
                $fh = fopen(DB_LOGFILE, 'a');
                fwrite($fh, $logstr);
                fclose($fh);
            }
        }
    }

    /**
     * Checks that object is a storable object.
     */
    protected function is_datajar($object)
    {
        return DatajarEngineBase::does_extend($object, "DatajarBase");
    }

    /**
     * Checks that object is storable or throw an exception.
     */
    protected function require_datajar($object)
    {
        if(!DatajarEngineBase::does_extend($object, "DatajarBase")) {
            throw new DatajarException(t("Provided object is not storable."));
        }
    }

    /**
     * Determines if the given object extends DatajarTypeBase.
     */
    public static function does_extend($object, $par_name)
    {
        if(!is_object($object)) {
            return false;
        }

        $refl = null;
        try {
            $refl = new ReflectionClass($object);
        }
        catch(ReflectionException $e) {
            return false;
        }

        while($refl = $refl->getParentClass()) {
            if($refl->getName() == $par_name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parses a connection string
     */
    protected function parse_conn_string($string)
    {
        $matches = array();
        preg_match('%^[^/]+?://(?:([^/@]*?)(?::([^/@:]+?)@)?([^/@:]+?)(?::([^/@:]+?))?)?/(.+)$%',
                   $string, $matches);
        return array('username' => $matches[1],
                     'password' => $matches[2],
                     'host'     => $matches[3],
                     'port'     => $matches[4],
                     'database' => $matches[5]);
    }

    /**
     * Checks for a usable backend.
     * @returns TRUE if the backend is ready, FALSE otherwise
     */
    //abstract function test_backend();
}

?>
