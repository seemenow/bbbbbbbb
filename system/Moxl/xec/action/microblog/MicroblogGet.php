<?php
/*
 * MicroblogGet.php
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

class MicroblogGet extends XECAction
{
    private $_to;
    
    public function request() 
    {
        $this->store();
        microblogGet($this->_to);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }
    
    public function handle($stanza) {
        $evt = new \Event();
        
        $to = current(explode('/',(string)$stanza->attributes()->to));
        $from = (string)$stanza->attributes()->from;

        if($stanza->pubsub->items->item) {
            foreach($stanza->pubsub->items->item as $item) {
                $p = new \Post();

                $query = \Post::query()->select()
                                           ->where(array(
                                                   'key' => $to,
                                                   'jid' => $from,
                                                   'nodeid' => (string)$item->attributes()->id));
                $data = \Post::run_query($query);

                if($data) {
                    $p = $data[0];
                }

                $p->setPost($item, $from);
                
                $p->run_query($p->query()->save($p));
            }
            
            $evt->runEvent('stream', $from);
        } else {
            $evt->runEvent('nostream');   
        }
    }
    
    public function errorFeatureNotImplemented($stanza) {
        $evt = new \Event();
        $evt->runEvent('nostream');
    }
    
    public function errorItemNotFound($stanza) {
        $evt = new \Event();
        $evt->runEvent('nostream');
    }
    
    public function errorNotAuthorized($stanza) {
        $evt = new \Event();
        $evt->runEvent('nostreamautorized');
    }

}
