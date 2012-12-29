<?php
/*
 * MicroblogPostDelete.php
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

class MicroblogPostDelete extends XECAction
{
    private $_to;
    private $_id;
    
    public function request() 
    {
        $this->store();
        microblogPostDelete($this->_to, $this->_id);
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
        $query = \Post::query()
                            ->where(
                                array(
                                    'key' => $this->_to,
                                    'nodeid' => $this->_id))
                            ->limit(0,1);
        $post = \Post::run_query($query);

        if($post) {
            $evt = new \Event();
            $post = $post[0];

            $post->delete();
            
            $evt->runEvent('postdeleted', $this->_id);
            $evt->runEvent('post');
        }
    }
    
    public function errorItemNotFound($stanza) {
        $query = \Post::query()
                            ->where(
                                array(
                                    'key' => $this->_to,
                                    'nodeid' => $this->_id))
                            ->limit(0,1);
        $post = \Post::run_query($query);

        if($post) {
            $evt = new \Event();
            $post = $post[0];

            $post->delete();
        }
        
        $evt = new \Event();
        
        $params = array($this->_id, 
                        t("This post doesn't exist on the server"));
        
        $evt->runEvent('postdeleteerror', $params);
    }
    
    public function errorFeatureNotImplemented($stanza) {
        $evt = new \Event();
        
        $params = array($this->_id, 
                        t("Your server doesn't support post deletetion"));
                        
        $evt->runEvent('postdeleteerror', $params);
    }
    
    public function errorNotAuthorized($stanza) {
        $evt = new \Event();
        
        $params = array($this->_id, 
                        t("You are not autorized to delete items on this node"));
                        
        $evt->runEvent('postdeleteerror', $params);
    }
    
    public function errorServiceUnavailable($stanza) {
        $evt = new \Event();
        
        $params = array($this->_id, 
                        t("Service unavaiable"));
        
        $evt->runEvent('postdeleteerror', $params);
    }

}
