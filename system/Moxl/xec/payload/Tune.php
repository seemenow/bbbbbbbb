<?php
/*
 * @file Tune.php
 * 
 * @brief Handle incoming presences
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

class Tune extends XECPayload
{
    public function handle($stanza, $parent = false) {
        //\movim_log($parent);
        /*$to = current(explode('/',(string)$parent->attributes()->to));
        $from = current(explode('/',(string)$parent->attributes()->from));
        
        if($stanza->item->geoloc->count() > 0) {
            $query = \Presence::query()->select()
                                       ->where(array(
                                               'key' => $to,
                                               'jid' => $from));
            $data = \Presence::run_query($query);
            
            if($data) {
                foreach($data as $p) {
                    $p->setPresenceGeoloc($stanza);
                    $p->run_query($p->query()->save($p));
                }
            }
        }*/
        //\movim_log($stanza);
        /*    
        $evt = new \Event();
            
        // Subscribe request
        if((string)$stanza->attributes()->type == 'subscribe') {
            $evt->runEvent('subscribe', (string)$stanza->attributes()->from);
            
        } else {    
            $p = new \Presence();
          
            $to = current(explode('/',(string)$stanza->attributes()->to));
            $jid = explode('/',(string)$stanza->attributes()->from);

            $query = \Presence::query()->select()
                                       ->where(array(
                                               'key' => $to,
                                               'jid' => $jid[0],
                                               'ressource' => $jid[1]))
                                       ->limit(0, 1);
            $data = \Presence::run_query($query);

            if($data) {
                $p = $data[0];
            }
            
            $p->setPresence($stanza);
            
            $p->run_query($p->query()->save($p));

            $evt->runEvent('presence', $p);
        }*/
    }
}
