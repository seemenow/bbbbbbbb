<?php
/**
 * @file loader.php
 *
 * @brief Loads up classes and stuff for Datajar.
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
 * Returns Datajar's version number.
 */
function datajar_version()
{
  return '0.01';
}

/**
 * Tests each driver for the presence of its backend.
 * @returns false if no backend was found, otherwise an
 * array with the list of back-ends and their individual
 * status.
 */
function datajar_test_backends()
{
    $base = dirname(__FILE__).'/';
    if($drivers_dir = opendir($base.'drivers')) {
        $backends = array();
        
        while($driver = readdir($drivers_dir)) {
            if(preg_match('/^DatajarEngine.+\.php$/', $driver)) {
                $drivername = substr($driver, 0, strlen($driver) - 4);
                require_once($base.'drivers/'.$driver);

                // 13 is the size of "DatajarEngine"
                $dname = strtolower(substr($drivername, 13));
                $backends[$dname] = call_user_func(array($drivername, 'test_backend'));
            }
        }

        return $backends;
    } else {
        return false;
    }
}

//* Function to easily load a Datajar Engine.
function datajar_load_driver($drivername)
{
    $base = dirname(__FILE__).'/';
    require_once($base."drivers/DatajarEngine".ucfirst(strtolower($drivername)).".php");
}

function load_datajar(array $drivers = array())
{
    $base = dirname(__FILE__).'/';

    require($base.'DatajarBase.php');
    require($base.'DatajarSQL.php');
    require($base.'DatajarCollection.php');
    require($base.'DatajarEngineBase.php');
    require($base.'DatajarEngineWrapper.php');
    require($base.'DatajarException.php');
    require($base.'DatajarSchema.php');
    require($base.'DatajarTypeBase.php');
    require($base.'DatajarType.php');
    require($base.'DatajarQuery.php');
   
    // Now loading the drivers
    if(!empty($drivers)){
	    foreach($drivers as $driver) {
	        $driver_init = $base.'drivers/'.$driver.'/init.php';
	        if(file_exists($driver_init)) {
	            require($driver_init);
	        }
		}
    }
}
?>
