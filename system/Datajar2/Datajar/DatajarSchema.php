<?php

/**
 * @file StorableSchema.php
 *
 * @brief Schema-aware class. Its job is to register and maintain relations
 * between DatajarBase-derived objects, an apply actions recursively to
 * update/delete in cascade and so on.
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

class DatajarSchema
{
    /**< Array that contains the relations. */
    private static $relations = array();
    private static $hierarchy = array();

    /** Constructor - not instanciable. */
    private function __construct()
    {
    }

    /**
     * Adds a relation between classes (and yes, classes are female).
     */
    public static function register_child_class($mother, $daughter, $onvar)
    {
        // Just checking...
        if(!is_array(self::$relations)) {
            self::$relations = array();
        }

        if(!isset(self::$relations[$mother])) {
            self::$relations[$mother] = array(array('class' => $daughter, 'var' => $onvar));
            self::$hierarchy[$mother] = array($daughter);
        } else {
            self::$relations[$mother][] = array('class' => $daughter, 'var' => $onvar);
            self::$hierarchy[$mother][] = $daughter;
        }
    }

    /**
     * Returns the list of classes children of the provided one.
     */
    public static function get_child_classes($mother)
    {
        // Just checking...
        if(!is_array(self::$hierarchy)) {
            self::$hierarchy = array();
        }

        if(isset(self::$hierarchy[$mother])) {
            return self::$hierarchy[$mother];
        } else {
            return array();
        }
    }

    /**
     * Returns the list of relations.
     */
    public static function get_relations($mother)
    {
        // Just checking...
        if(!is_array(self::$relations)) {
            self::$relations = array();
        }

        if(isset(self::$relations[$mother])) {
            return self::$relations[$mother];
        } else {
            return array();
        }
    }

    /**
     * Cascade creates children.
     */
    function cascade_create(&$datajar, $mother)
    {
        $children = self::get_child_classes($mother);
        foreach($children as $child) {
            $daughter = new $child();
            $daughter->create($datajar);
        }
    }

    /**
     * Cascade drop children.
     */
    function cascade_drop(&$datajar, $mother)
    {
       $children = self::get_child_classes($mother);
        foreach($children as $child) {
            $daughter = new $child();
            $daughter->drop($datajar);
        }
    }
}

?>