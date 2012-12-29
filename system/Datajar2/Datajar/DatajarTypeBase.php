<?php

/**
 * @file DatajarTypes.php
 *
 * @brief Defines types of SQLite.
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

/**
 * Handy exception.
 */
class DatajarTypeException extends Exception
{
    public function __construct($message) { parent::__construct($message); }
}

/**
 * Basic datajar class that is extended by all others.
 */
class DatajarTypeBase
{
    protected $val;

    public function __construct()
    {
        $this->params = func_get_args();
    }

    /**
     * Extend to check input types.
     */
    protected function check_input($value)
    {
        return true;
    }

    public function setval($value)
    {
        if($this->check_input($value)) {
            $this->val = $value;
            return $value;
        } else {
            throw new DatajarTypeException(t("Provided value `%s' is not of correct type.", $value));
        }
    }

    public function getval()
    {
        return $this->val;
    }

    public function gettype()
    {
        return get_class($this);
    }

    public function getme()
    {
        return $this;
    }

    public function apply($func, $args)
    {
        return call_user_func(array($this->val, $func), $args);
    }
}

class DatajarTypeBigInt extends DatajarTypeBase
{
    protected function check_input($value)
    {
        return is_numeric($value);
    }
}

class DatajarTypeBool extends DatajarTypeBase
{
}

class DatajarTypeVarChar extends DatajarTypeBase
{
    public $length;

    function __construct($length)
    {
        $this->length = $length;
    }
}

class DatajarTypeDate extends DatajarTypeBase
{
}

class DatajarTypeDateTime extends DatajarTypeBase
{
}

class DatajarTypeDecimal extends DatajarTypeBase
{
    public $length;
    public $decimal_places;

    function __construct($length, $decimal_places)
    {
        $this->length = $length;
        $this->decimal_places = $decimal_places;
    }
}

class DatajarTypeFloat extends DatajarTypeBase
{
}

class DatajarTypeInt extends DatajarTypeBase
{
}

class DatajarTypeText extends DatajarTypeBase
{
}

class DatajarTypeBlob extends DatajarTypeBase
{
}

class DatajarTypeForeignKey extends DatajarTypeBase
{
    public $model;
    public $field;

    public function __construct($model, $field = 'id')
    {
        $this->model = $model;
        $this->field = $field;
    }
}

?>