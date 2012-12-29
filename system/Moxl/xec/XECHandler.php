<?php
/*
 * @file XECHandler.php
 * 
 * @brief Handle incoming XMPP request and dispatch them to the correct 
 * XECElement
 * 
 * Copyright 2012 edhelas <edhelas@edhelas-laptop>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * 
 */

namespace moxl;


class XECHandler {

    /**
     * Constructor of class XECHandler.
     *
     * @return void
     */
    static public function handle(\SimpleXMLElement $stanza)
    {
        // We get the cached instances
        $sess = \Session::start(APP_NAME);
        $_instances = $sess->get('xecinstances');
        
        foreach($stanza->children() as $child) {
            $id = 0;
            $element = '';
            
            // Id verification in the returned stanza
            if($child->getName() == 'iq') {
                $id = (int)$child->attributes()->id;
                $element = 'iq';
            }

            if($child->getName() == 'presence') {
                $id = (int)$child->attributes()->id;
                $element = 'presence';
            }
            

            if(
                $id != 0 && 
                $_instances != false && 
                array_key_exists($id, $_instances)
              ) {
                // We search an existent instance
                if(!array_key_exists($id, $_instances))
                    MoxlLogger::log('XECHandler : Memory instance not found');
                else {
                    $instance = $_instances[$id];
                    
                    $action = unserialize($instance['object']);
                    
                    // XMPP returned an error
                    if($child->error) {
                        $errors = $child->error->children();

                        $errorid = XECHandler::formatError($errors->getName());

                        MoxlLogger::log('XECHandler : '.$id.' - '.$errorid);

                        /* If the action has defined a special handler
                         * for this error
                         */
                        if(method_exists($action, $errorid))
                            $action->$errorid();
                    } else {
                        // We launch the object handle
                        $action->handle($child);
                    }
                    // We clean the object from the cache
                    unset($_instances[$id]);
                    
                    $sess->set('xecinstances', $_instances);
                }
            } else {
                MoxlLogger::log('XECHandler : Not an XMPP ACK');

                XECHandler::handleNode($child);
                
                if($child->count() > 0) {
                    foreach($child as $s1) {
                        XECHandler::handleNode($s1, $child);  
                        if($s1->count() > 0) {
                            foreach($s1 as $s2)
                                XECHandler::handleNode($s2, $child);  
                        }
                    }
                }
            }
        }
    }
    
    static public function handleNode($s, $sparent = false) {
        require('XECHandler.array.php');
        
        $name = $s->getName();
        $ns = $s->getNamespaces();
        $node = (string)$s->attributes()->node;
        
        if(is_array($ns))
            $ns = current($ns);

        $hash = md5($name.$ns.$node);

        MoxlLogger::log('XECHandler : Searching a payload for "'.$name . ':' . $ns . ' [' . $node . ']", "'.$hash.'"'); 

        $base = __DIR__.'/';
        if(file_exists($base.'payload/'.$hashToClass[$hash].'.php')) {
            require_once($base.'payload/'.$hashToClass[$hash].'.php');
            $classname = '\\moxl\\'.$hashToClass[$hash];
            
            if(class_exists($classname)) {
                $payload_class = new $classname();
                $payload_class->handle($s, $sparent);
            } else {
               MoxlLogger::log('XECHandler : Payload class "'.$hashToClass[$hash].'" not found'); 
            }
        } else {
            MoxlLogger::log('XECHandler : Payload file "'.$hashToClass[$hash].'" not found');
        }
    }

    /* A simple function to format a error-string-text to a
     * camelTypeText 
     */
    static public function formatError($string) {

        $words = explode('-', $string);
        $f = 'error';
        foreach($words as $word)
            $f .= ucfirst($word);

        return $f;
    }

}
