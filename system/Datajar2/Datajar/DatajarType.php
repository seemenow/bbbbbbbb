<?php

/**
 * @file DatajarType.php
 *
 * @brief Convenience class to spawn types.
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

class DatajarType
{
    private function __construct() {}

    public static function bigint()
    {
        return new DatajarTypeBigInt();
    }

    public static function bool()
    {
        return new DatajarTypeBool();
    }

    public static function varchar($length)
    {
        return new DatajarTypeVarChar($length);
    }

    public static function date()
    {
        return new DatajarTypeDate();
    }

    public static function datetime()
    {
        return new DatajarTypeDateTime();
    }

    public static function decimal($length, $decimal_places)
    {
        return new DatajarTypeDecimal($length, $decimal_places);
    }

    public static function float()
    {
        return new DatajarTypeFloat();
    }

    public static function int()
    {
        return new DatajarTypeInt();
    }

    public static function text()
    {
        return new DatajarTypeText();
    }

    public static function blob()
    {
        return new DatajarTypeBlob();
    }

    public static function foreignkey($mother, $var, $child)
    {
        // Attaching to model.
        DatajarSchema::register_child_class($mother, $child, $var);
        return new DatajarTypeForeignKey($child);
    }
}

?>
