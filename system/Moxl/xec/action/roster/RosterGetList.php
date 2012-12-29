<?php
/*
 * RosterGetList.php
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

class RosterGetList extends XECAction
{
    public function request() 
    {
        $this->store();
        rosterGet();
    }
    
    
    public function handle($stanza) {
        $to = current(explode('/',(string)$stanza->attributes()->to));
        
        $evt = new \Event();
        
        // We get all our contact
        $query = \RosterLink::query()->select()
                                   ->where(array(
                                           'key' => $to));
        $data = \RosterLink::run_query($query);
        
        foreach($stanza->query->item as $item) {
            
            // We search if the contact exist in the database 
            $found = false;
            foreach($data as $cd) {
                if($cd->jid->getval() == (string)$item->attributes()->jid) {
                    $found = $cd;
                    break;
                }
            }

            // If not found, we create it
            if($found == false) {
                $c = new \RosterLink();
            } else {
                $c = $found;
            }

            $c->key->setval($to);
            $c->jid->setval((string)$item->attributes()->jid);
            
            if(isset($item->attributes()->name) && (string)$item->attributes()->name != '')
                $c->rostername->setval((string)$item->attributes()->name);
            else
                $c->rostername->setval((string)$item->attributes()->jid);
            $c->rosterask->setval((string)$item->attributes()->ask);
            $c->rostersubscription->setval((string)$item->attributes()->subscription);
            $c->group->setval((string)$item->group);
            
            $c->run_query($c->query()->save($c));
        }
        
        $evt->runEvent('roster');
    }
    
    public function load($key) {}
    public function save() {}
}
