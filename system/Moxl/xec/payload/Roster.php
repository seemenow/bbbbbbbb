<?php
/*
 * @file Roster.php
 * 
 * @brief Handle incoming roster request
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

class Roster extends XECPayload
{
    public function handle($stanza, $parent = false) {
        if((string)$stanza->attributes()->type == 'set') 
        {
            $evt = new \Event();
            
            $from = current(explode('/',(string)$stanza->attributes()->from));
            $jid = current(explode('/',(string)$stanza->query->item->attributes()->jid));
            $subscription = current(explode('/',(string)$stanza->query->item->attributes()->subscription));
            $group = current(explode('/',(string)$stanza->query->item->group));
        
            $c = new \RosterLink();
            

            $query = \RosterLink::query()->select()
                                       ->where(array(
                                               'key' => $from,
                                               'jid' => $jid));
            $data = \RosterLink::run_query($query);

            if($data) {
                $c = $data[0];
            }

            $c->key->setval($from);
            $c->jid->setval($jid);
            $c->group->setval($group);
            $c->rostersubscription($subscription);
            
            $c->run_query($c->query()->save($c));
            
            $evt->runEvent('roster');
        }
    }
}
