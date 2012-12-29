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

class MicroblogCommentsGet extends XECAction
{
    private $_to;
    private $_id;
    
    public function request() 
    {
        $this->store();
        microblogCommentsGet($this->_to, $this->_id);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }
    
    public function handle($stanza) {
        $evt = new \Event();
        
        $to = current(explode('/',(string)$stanza->attributes()->to));
        $from = (string)$stanza->attributes()->from;

        list($xmlns, $parent) = explode("/", (string)$stanza->pubsub->items->attributes()->node);

        if($stanza->pubsub->items->item) {
            
            $comments = array();

            foreach($stanza->pubsub->items->item as $item) {
                $c = new \Post();

                $query = \Post::query()->select()
                                           ->where(array(
                                                   'key' => $to,
                                                   'jid' => $from,
                                                   'nodeid' => (string)$item->attributes()->id));
                $data = \Post::run_query($query);

                if($data) {
                    $c = $data[0];
                }

                $c->setPost($item, $from, $parent);
                
                array_push($comments, $c);
                
                $c->run_query($c->query()->save($c));
            }
            
            $evt->runEvent('comment', $parent, $comments);
        } else {
            $evt->runEvent('nocomment', $parent);   
        }
    }
    
    public function errorFeatureNotImplemented($stanza) {
        $evt = new \Event();
        $evt->runEvent('nocommentstream', $this->_id);
    }
    
    public function errorItemNotFound($stanza) {
        $c = new \Post();

        $query = \Post::query()->select()
                                   ->where(array(
                                           'nodeid' => $this->_id,
                                           'commentson' => '1'));
        $data = \Post::run_query($query);

        if($data) {
            $c = $data[0];
        
            $c->commentson->setval(0);
            $c->run_query($c->query()->save($c));
        }

        
        $evt = new \Event();
        $evt->runEvent('nocommentstream', $this->_id);
    }
    
    public function errorNotAuthorized($stanza) {
        $evt = new \Event();
        $evt->runEvent('nostreamautorized', $this->_id);
    }

}
