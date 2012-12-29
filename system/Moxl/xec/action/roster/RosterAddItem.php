<?php
/*
 * RosterAddItem.php
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

class RosterAddItem extends XECAction
{
    private $_to;
    
    public function request() 
    {
        $this->store();
        rosterAdd($this->_to);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }
    
    public function handle($stanza) 
    {
        $from = current(explode('/',(string)$stanza->attributes()->from));
        
        $c = new \RosterLink();

        $query = \RosterLink::query()->select()
                                   ->where(array(
                                           'key' => $from,
                                           'jid' => $this->_to));
        $data = \RosterLink::run_query($query);

        if($data) {
            $c = $data[0];
        }

        $c->key->setval($from);
        $c->jid->setval($this->_to);
        
        if(isset($stanza->item->attributes()->name) && (string)$stanza->item->attributes()->name != '')
            $c->rostername->setval((string)$stanza->item->attributes()->name);
        else
            $c->rostername->setval((string)$stanza->item->attributes()->jid);

        $c->rostersubscription->setval((string)$stanza->item->attributes()->subscription);
        
        $c->run_query($c->query()->save($c));
        
        $evt = new \Event();
        $evt->runEvent('roster');
    }
    
    public function errorServiceUnavailable() 
    {
        var_dump('Handle the Error !');
    }
    
    public function load($key) {}
    public function save() {}
}
