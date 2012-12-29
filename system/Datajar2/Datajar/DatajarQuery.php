<?php

/**
 * @file DatajarQuery.php
 *
 * @brief Object-oriented select query wrapper for storable objects.
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

class DatajarQuery
{
    protected $objecttype;
	protected $action;
    protected $where = array();
	protected $join = false;
    protected $orderby = false;
    protected $desc = false;
    protected $limit = null;
	protected $subject = null;

	// Action constants (PHP doesn't support enums...)
	const ACTION_NONE	= 0;
	const ACTION_SELECT = 1;
	const ACTION_SAVE	= 2;
	const ACTION_DELETE = 3;
	const ACTION_DROP	= 4;
	const ACTION_JOIN   = 5;
	const ACTION_CREATE = 6;

    public function __construct($objecttype)
    {
        $this->objecttype = $objecttype;
		$this->action = self::ACTION_NONE;
    }

	// Functional mapping for actions.
	public function create(DatajarBase $object)
	{
		$this->action = self::ACTION_CREATE;
		$this->subject = $object;
		$this->objecttype = $object->objname();

		return $this;
	}

	public function select($objecttype = false)
	{
		$this->action = self::ACTION_SELECT;
		if($objecttype) {
			$this->objecttype = $objecttype;
		}
		return $this;
	}

	public function save(DatajarBase $object)
	{
		$this->action = self::ACTION_SAVE;
		$this->subject = $object;
		$this->objecttype = $object->objname();
		return $this;
	}

	public function delete($object = null)
	{
		$this->action = self::ACTION_DELETE;
		if($object) {
			$this->subject = $object;
			$this->objecttype = $object->objname();
		}
		return $this;
	}

	public function drop(DatajarBase $object)
	{
		$this->action = self::ACTION_DROP;
		$this->subject = $object;
		$this->objecttype = $object->objname();
		return $this;
	}

    public function where(array $cond = null)
    {
		// Backwards compatibility
		if($this->action == self::ACTION_NONE) {
			$this->action = self::ACTION_SELECT;
		}
		
        $this->where = $cond;
        return $this;
    }

	/**
	 * Joins two tables on the query.
	 */
	public function join($tbl, array $cond)
	{
		$this->action = self::ACTION_JOIN;
		$this->join = array('class' => $tbl, 'where' => $cond);
		return $this;
	}

    public function orderby($col, $desc = false)
    {
        $this->orderby = $col;
        $this->desc = $desc;
        return $this;
    }

    public function limit($start, $length)
    {
        $this->limit = array($start, $length);
        return $this;
    }

    public function commit()
    {
        return $objecttype::run_query($this);
    }

	// Accessors
    public function get_object() { return $this->objecttype; }
    public function get_cond() { return $this->where; }
    public function get_orderby() { return $this->orderby; }
    public function get_desc() { return $this->desc; }
    public function get_limit() { return $this->limit; }
	public function get_join() { return $this->join; }
	public function get_action() { return $this->action; }
	public function get_subject() { return $this->subject; }
}

?>
