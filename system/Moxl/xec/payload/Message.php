<?php
/*
 * @file Message.php
 * 
 * @brief Handle incoming messages
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

class Message extends XECPayload
{
    public function handle($stanza, $parent = false) {        
        $jid = explode('/',(string)$stanza->attributes()->from);
        $to = current(explode('/',(string)$stanza->attributes()->to));

        $evt = new \Event();
        
        if($stanza->composing)
            $evt->runEvent('composing', $jid[0]);
        if($stanza->paused)
            $evt->runEvent('paused', $jid[0]);
        if($stanza->gone)
            $evt->runEvent('gone', $jid[0]);
        if($stanza->body) {
            $m = new \Message();
            
            $m->key->setval($to);
            $m->to->setval($to);
            $m->from->setval($jid[0]);
            
            $m->type->setval("chat");
            
            $m->body->setval((string)$stanza->body);
            
            $m->published->setval(date('Y-m-d H:i:s'));
            $m->delivered->setval(date('Y-m-d H:i:s'));

            $m->run_query($m->query()->save($m));

            $evt->runEvent('message', $m);
        }
    }
}
