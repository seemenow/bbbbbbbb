<?php

/**
 * @file DatajarEngine.php
 *
 * @brief Implements a datajar driver for sqlite.
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Datajar.  If not, see <http://www.gnu.org/licenses/>.
 */

class DatajarEngineSqlite extends DatajarEngineBase
{
    protected $db;
	protected $db_file;

    // Loading config and attempting to connect.
    public function __construct($conn = "")
    {
        $args = func_get_args();
        if($conn != "") {
            $this->init($conn);
        }
    }

    public function init($conn_string)
    {
        $conn_details = $this->parse_conn_string($conn_string);

        $this->db_file = $conn_details['database'];

        // Checking the file can be accessed.
        if($this->db_file == "") {
            throw new DatajarException(t("The database file must be specified."));
        }

        // OK, trying to open the file.
       try{
        	$this->db = new SQLite3($this->db_file);
        }catch (Exception $e){
            throw new DatajarException(t("The database %s couldn't be opened.", $db_file));
        }


		if(method_exists($this->db, 'busyTimeout')) { // Mac OS or maybe older PHP
			$this->db->busyTimeout(30000); // 30s lock timeout.
		}
    }

    public static function escape($data) {
        return SQLite3::escapeString($data);
    }

    public function __destruct()
    {
        //$this->close();
    }

    public function close()
    {
        if($this->db) {
            $this->db->close();
            $this->db = NULL;
        }
    }

    /**
     * Checks SQLite errors. Throws a DatajarException if there was an error
     * during the last query.
     */
    protected function errors()
    {
        $error = $this->db->lastErrorCode();
        if($error != 0) {
            throw new DatajarException(
                sprintf("[%s] %s `%s'",
						$error,
						$this->db->lastErrorMsg(),
						$this->db_file));
        }
    }

    /**
     * SQLite-specific routine with error check included.
     */
    protected function query($statement, $num_cols_for_first_tbl = 0)
    {
        $this->log($statement);

        if(strtoupper(substr(trim($statement), 0, 6)) == "SELECT") {
            $res = $this->db->query($statement);
            $this->errors();

            $table = array();
            while($row = $res->fetchArray(SQLITE3_NUM))
            {
				$named_row = array();
				for($num_col = 0; $num_col < $res->numColumns(); $num_col++) {
					// This is an ugly hack to guess the table number... Very dirty...
					$prefix = '';
					if($num_cols_for_first_tbl) {
						if($num_col >= $num_cols_for_first_tbl) {
							$prefix = 'table2.';
						} else {
							$prefix = 'table1.';
						}
					}
					
					$named_row[$prefix . $res->columnName($num_col)] = $row[$num_col];
				}
                $table[] = $named_row;
            }
            return $table;
        } else {
            $answer = $this->db->exec($statement);
            $this->errors();
            return $answer;
        }
    }

    protected function lastId()
    {
        return $this->db->lastInsertRowId();
    }

	/**
     * Generates the creation statement.
     */
    protected function create_stmt(&$type)
    {
        $typename = $type['val']->gettype();
        $def = $type['name'] . ' ';
        switch($typename) {
        case 'DatajarTypeBigInt':
            $def.= 'BIGINT';
            break;
        case 'DatajarTypeBool':
            $def.= 'BOOLEAN';
            break;
        case 'DatajarTypeVarChar':
            $def.= 'VARCHAR('.$type['val']->length.')';
            break;
        case 'DatajarTypeDate':
            $def.= 'DATE';
            break;
        case 'DatajarTypeDateTime':
            $def.= 'DATETIME';
            break;
        case 'DatajarTypeDecimal':
            $def.= 'DECIMAL('.$type['val']->length.', '.$type['val']->decimal_places.')';
            break;
        case 'DatajarTypeFloat':
            $def.= 'FLOAT';
            break;
        case 'DatajarTypeInt':
            $def.= 'INTEGER';
            break;
        case 'DatajarTypeText':
            $def.= 'TEXT';
            break;
        case 'DatajarTypeBlob':
            $def.= 'BLOB';
            break;
        case 'DatajarTypeForeignKey':
            $def.= 'INTEGER NOT NULL REFERENCES '.$type['val']->model.'('.$type['val']->field.')';
            break;
        }

        return $def;
    }

    public function create(DatajarBase $object)
    {
        $this->require_datajar($object);

        $proto = $object->prototype();

        $stmt = 'CREATE TABLE IF NOT EXISTS "'.$this->obj_name($object).'" ('.
            '"id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, ';
        foreach($proto as $prop) {
            $stmt .= $this->create_stmt($prop) . ', ';
        }

        // Stripping the extra ', ' and closing the statement.
        $stmt = substr($stmt, 0, -2) . ');';

        return $this->query($stmt);
    }

    public function save(DatajarBase $object)
    {
        $this->require_datajar($object);

        $stmt = "";

        $props = $object->prototype();

        if(!$object->id) {
            $stmt = "INSERT INTO " . $this->obj_name($object);

            $cols = "";
            $vals = "";
            foreach($props as $prop) {
                $cols.= $prop['name'] . ', ';
                if(DatajarEngineBase::does_extend($prop['val'], "DatajarBase")) {
                    $vals.= '"' . $prop['val']->id . '", ';
                } else {
                    $vals.= '"' . $prop['val']->getval() . '", ';
                }
            }

            $stmt.= '(' . substr($cols, 0, -2) . ')';
            $stmt.= ' VALUES(' . substr($vals, 0, -2) . ');';

            $this->query($stmt);
            $object->setid($this->lastId());
        } else {
            $stmt = "UPDATE " . $this->obj_name($object) . " SET ";

            $cols = "";
            $vals = "";
            foreach($props as $prop) {
                $stmt.= $prop['name'] . '=';
                if(DatajarEngineBase::does_extend($prop['val'], "DatajarBase")) {
                    $stmt.= '"' . $prop['val']->id . '", ';
                } else {
                    $stmt.= '"' . $prop['val']->getval() . '", ';
                }
            }

            $stmt = substr($stmt, 0, -2) . ' WHERE id="' . $object->id . '"';

            return $this->query($stmt);
        }
    }

    public function delete(DatajarBase $object)
    {
        $this->require_datajar($object);

        // Does the object exist in the datajar?
        if($object->id) {
            $stmt = "DELETE FROM " . $this->obj_name($object) . " WHERE id=\"" . $object->id . "\";";

            $result = $this->query($stmt);

            if($result) {
                $object->clearid();
            }

            return $result;
        }
    }

    public function drop($object)
    {
        $this->require_datajar($object);

        $stmt = 'DROP TABLE IF EXISTS '.$this->obj_name($object).';';

        $result = $this->query($stmt);

        if($result) {
            $object->clearid();
        }

        return $result;
    }

    /**
     * Returns data relative to an object as an array.
     */
    public function load(DatajarBase $object, array $cond)
    {
        $stmt = "SELECT * FROM " . $object->objname();

        if(count($cond) > 0) {
            $stmt.= " WHERE " . DatajarSQL::gen_where($cond, "'", "'", array($this, 'escape'));
        }

        $this->log($stmt);

        $data = $this->query($stmt);

        if(count($data) < 1) {
            return false;
        }

        $data = $data[0];

        // Populating the object.
        $props = $object->prototype();

        // Setting the id first.
        $object->setid($data['id']);

        foreach($props as $prop) {
            if(isset($data[$prop['name']])) {
                $object->__set($prop['name'], $data[$prop['name']]);
            }
        }

        return true;
    }

    public function run(DatajarQuery $query)
    {
		$stmt = '';
		$load_id = false;
		$wipe_id = false;

		switch($query->get_action()) {
		case DatajarQuery::ACTION_CREATE:
			$proto = $query->get_subject()->prototype();

			$stmt = 'CREATE TABLE IF NOT EXISTS "'.$query->get_subject()->objname().'" ('.
				'"id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, ';
			foreach($proto as $prop) {
				$stmt .= $this->create_stmt($prop) . ', ';
			}

			// Stripping the extra ', ' and closing the statement.
			$stmt = substr($stmt, 0, -2) . ');';
			break;
		case DatajarQuery::ACTION_JOIN:
			$stmt = "SELECT * FROM '" . $query->get_object() . "'";

			$join = $query->get_join();
			$stmt.= ' JOIN \'' . $join['class'] . '\' ON ' .
				DatajarSQL::gen_where($join['where'], "", "", array($this, 'escape'));
		case DatajarQuery::ACTION_SELECT:
			if(!$stmt) {
				$stmt = "SELECT * FROM '" . $query->get_object() . "'";
			}

			if(count($query->get_cond())) {
				$stmt.= ' WHERE ' . DatajarSQL::gen_where($query->get_cond(),
														  "'", "'", array($this, 'escape'));
			}

			if($query->get_orderby()) {
				$stmt.= " ORDER BY " . $query->get_orderby();
				if($query->get_desc()) {
					$stmt.= ' DESC';
				}
			}

			$limit = $query->get_limit();
			if(count($limit)) {
				$stmt.= ' LIMIT ' . $limit[0] . ', ' . $limit[1];
			}
			break;
		case DatajarQuery::ACTION_SAVE:
			$props = $query->get_subject()->prototype();
			if(!$query->get_subject()->id) {
				$load_id = true;
				$stmt = "INSERT INTO '" . $query->get_object() . "'";
				$cols = "";
				$vals = "";
				foreach($props as $prop) {
					$cols.= "'" . $prop['name'] . "', ";
					// Is that a linked object?
					if(self::does_extend($prop['val'], "DatajarBase")) {
						$vals.= "'" . $prop['val']->id . "', ";
					} else {
						$vals.= "'" . $this->escape($prop['val']->getval()) . "', ";
					}
				}

				$stmt.= '(' . substr($cols, 0, -2) . ')';
				$stmt.= ' VALUES(' . substr($vals, 0, -2) . ')';
			} else {
				$stmt = "UPDATE '" . $query->get_object() . "' SET ";
				$cols = '';
				$vals = '';
				foreach($props as $prop) {
					$stmt.= "'" . $prop['name'] . "'=";
					if(self::does_extend($prop['val'], 'DatajarBase')) {
						$stmt.= "'" . $prop['val']->id . "', ";
					} else {
						$stmt.= "'" . $this->escape($prop['val']->getval()) . "', ";
					}
				}
				$stmt = substr($stmt, 0, -2);
				if(count($query->get_cond())) {
					$stmt.= ' WHERE ' . DatajarSQL::gen_where($query->get_cond(), "'", "'", array($this, 'escape'));
				}
				else if(is_object($query->get_subject())) {
					$stmt.= " WHERE id='" . $query->get_subject()->id . "'";
				}
			}
			break;
		case DatajarQuery::ACTION_DELETE:
			$stmt = "DELETE FROM '" . $query->get_object() . "'";
			if(count($query->get_cond())) {
				$stmt.= ' WHERE ' . DatajarSQL::gen_where($query->get_cond(), "'", "'", array($this, 'escape'));
			}
			else if(is_object($query->get_subject())) {
				$stmt.= " WHERE id='" . $query->get_subject()->id . "'";
				$wipe_id = true;
			}
			break;
		case DatajarQuery::ACTION_DROP:
			$stmt = "DROP TABLE '" . $query->get_object() . "'";
			if(is_object($query->get_subject())) {
				$wipe_id = true;
			}
			break;
		default: // Also works for ACTION_NONE
			throw new Exception("Unknown database action");
		}

		if($query->get_action() == DatajarQuery::ACTION_SELECT) {
			// Getting the number of columns in the object.
            $objecttype = $query->get_object();
			$data = $this->query($stmt);
            $objs = array();

            foreach($data as $row) {
                $object = new $objecttype();
                // Populating the object.
                $props = $object->prototype();

                $object->setid($row['id']);

                foreach($props as $prop) {
                    if(isset($row[$prop['name']])) {
                        $object->__set($prop['name'], $row[$prop['name']]);
                    }
                }

                $objs[] = $object;
            }
			
            return $objs;
		}
		else if($query->get_action() == DatajarQuery::ACTION_JOIN) {
			// Getting the number of columns in the object.
            $objecttype1 = $query->get_object();
			$join = $query->get_join();
			$objecttype2 = $join['class'];
			
			$object = new $objecttype1();
			$data = $this->query($stmt, count($object->prototype()) + 1); // + 1 for ID.

			// Mapping names.
			$tables = array('table1' => $objecttype);
			$tables['table2'] = $join['class'];

            $objs = array();
            foreach($data as $row) {
                $object1 = new $objecttype1();
				$object2 = new $objecttype2();
				
                // Populating the objects.
                $props1 = $object1->prototype();
				$props2 = $object2->prototype();

                $object1->setid($row['table1.id']);
                $object2->setid($row['table2.id']);

				// Object 1
                foreach($props1 as $prop) {
                    if(isset($row['table1.'.$prop['name']])) {
                        $object1->__set($prop['name'], $row['table1.'.$prop['name']]);
                    }
                }
				
				// Object 2
				foreach($props2 as $prop) {
                    if(isset($row['table2.'.$prop['name']])) {
                        $object2->__set($prop['name'], $row['table2.'.$prop['name']]);
                    }
                }

                $objs[] = array($object1, $object2);
            }
			
            return $objs;
		}
		else if($load_id) {
			$result = $this->query($stmt);
			$query->get_subject()->setid($this->lastId());
			return $result;
		}
		else if($wipe_id) {
			$result = $this->query($stmt);
			if($result) {
				$query->get_subject()->clearid();
			}
			return $result;
		}
		else {
			return $this->query($stmt);
		}
    }

    static function test_backend()
    {
        return class_exists('SQLite3');
    }
}

?>
