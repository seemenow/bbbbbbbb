<?php

/**
 * @file DatajarException.php
 *
 * @brief Exception specialised in Databases.
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

class DatajarException extends Exception
{
    function __construct($message, $detail = "", $code = -999)
    {
        $error = sprintf("Datajar error: %s", $message);

        if($code != -999) {
            $error.= "\n". sprintf("Code: %d", $code);
        }

        if($detail != "") {
            $error.= "\n" . $detail;
        }

        parent::__construct($error);
    }

    function __toString() {
        return $this->message;
    }
}

?>
