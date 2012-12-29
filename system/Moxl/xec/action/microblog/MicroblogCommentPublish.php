<?php
/*
 * MicroblogCommentPublish.php
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

class MicroblogCommentPublish extends XECAction
{
    private $_to;
    private $_parentid;
    private $_content;
    private $_name;
    private $_from;
    
    public function request() 
    {
        $this->store();
        microblogCommentPublish($this->_to, $this->_parentid, $this->_content, $this->_name, $this->_from);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setParentId($parentid)
    {
        $this->_parentid = $parentid;
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
    
    public function setFrom($from)
    {
        $this->_from = $from;
        return $this;
    }
        
    public function handle($stanza) {
        $evt = new \Event();
        $c = new \Post();
        
        $c->key->setval($this->_from);
        $c->jid->setval((string)$stanza->attributes()->from);
        
        $c->uri->setval($this->_from);
        $c->nodeid->setval((string)$stanza->pubsub->publish->item->attributes()->id);
        $c->parentid->setval($this->_parentid);
        $c->content->setval($this->_content);
        
        $c->published->setval(date('Y-m-d H:i:s'));
        $c->updated->setval(date('Y-m-d H:i:s'));
        
        $c->run_query($c->query()->save($c));
        
        $evt->runEvent('comment', $this->_parentid);
    }
    
    public function errorFeatureNotImplemented($stanza) {
        $evt = new \Event();
        $evt->runEvent('commentpublisherror');
    }
    
    public function errorNotAuthorized($stanza) {
        $evt = new \Event();
        $evt->runEvent('commentpublisherror');
    }
    
    public function errorServiceUnavailable($stanza) {
        $evt = new \Event();
        $evt->runEvent('commentpublisherror');  
    }

}
