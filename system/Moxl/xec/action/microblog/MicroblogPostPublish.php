<?php
/*
 * MicroblogPostPublish.php
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

class MicroblogPostPublish extends XECAction
{
    private $_to;
    private $_id;
    private $_content;
    private $_name;
    
    public function request() 
    {
        $this->store();
        microblogPostPublish($this->_to, $this->_id, $this->_content, $this->_name);
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

    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }
    
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }
    
    public function handle($stanza) {       
        $evt = new \Event();
        $c = new \Post();
        
        $from = (string)$stanza->attributes()->from;
        
        $c->key->setval($this->_to);
        $c->jid->setval($this->_to);
        
        $c->uri->setval($this->_from);
        $c->nodeid->setval($this->_id);
        $c->content->setval($this->_content);
        
        $c->published->setval(date('Y-m-d H:i:s'));
        $c->updated->setval(date('Y-m-d H:i:s'));
        
        $c->commentson->setval(1);
        $c->commentplace->setval($this->_to);
        
        $c->run_query($c->query()->save($c));
        
        $evt->runEvent('postpublished', $c);
    }
    
    public function errorFeatureNotImplemented($stanza) {
        $evt = new \Event();
        $evt->runEvent('postpublisherror', t("Your server doesn't support post publication"));
    }
    
    public function errorNotAuthorized($stanza) {
        $evt = new \Event();
        $evt->runEvent('postpublisherror', t("You are not autorized to publish on this node"));
    }
    
    public function errorServiceUnavailable($stanza) {
        $evt = new \Event();
        $evt->runEvent('postpublisherror', t("Service unavaiable"));  
    }
    
    public function errorForbidden($stanza) {
        $this->errorFeatureNotImplemented($stanza);
    }

}
