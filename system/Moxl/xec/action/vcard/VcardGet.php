<?php
/*
 * VcardGet.php
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

class VcardGet extends XECAction
{
    private $_to;
    
    public function request() 
    {
        $this->store();
        vcardGet($this->_to);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }
    
    public function handle($stanza) {
        $to = current(explode('/',(string)$stanza->attributes()->to));
        $jid = current(explode('/',(string)$stanza->attributes()->from));
        
        $evt = new \Event();

        $c = new \Contact();

        $query = \Contact::query()->select()
                                   ->where(array(
                                           'jid' => $jid));
        $data = \Contact::run_query($query);

        if($data) {
            $c = $data[0];
        }

        $c->jid->setval($jid);
        
        $date = strtotime((string)$stanza->vCard->BDAY);
        if($date != false) 
            $c->date->setval(date('Y-m-d', $date)); 
        
        $c->name->setval((string)$stanza->vCard->NICKNAME);
        $c->fn->setval((string)$stanza->vCard->FN);
        $c->url->setval((string)$stanza->vCard->URL);

        $c->gender->setval((string)$stanza->vCard->{'X-GENDER'});
        $c->marital->setval((string)$stanza->vCard->MARITAL->STATUS);

        $c->email->setval((string)$stanza->vCard->EMAIL->USERID);
        
        $c->adrlocality->setval((string)$stanza->vCard->ADR->LOCALITY);
        $c->adrpostalcode->setval((string)$stanza->vCard->ADR->PCODE);
        $c->adrcountry->setval((string)$stanza->vCard->ADR->CTRY);
        
        $c->phototype->setval((string)$stanza->vCard->PHOTO->TYPE);
        $c->photobin->setval((string)$stanza->vCard->PHOTO->BINVAL);
        
        $c->desc->setval((string)$stanza->vCard->DESC);

        $c->public->setval(0);
        
        $c->run_query($c->query()->save($c));
        
        // RosterLink truename value
        $query = \RosterLink::query()->select()
                                   ->where(array(
                                           'key' => $to,
                                           'jid' => $jid));
        $data = \RosterLink::run_query($query);

        if($data) {
            $r = $data[0];

            $r->key->setval($to);
            $r->jid->setval($jid);
            $r->realname->setval($c->getTrueName());
            
            $r->run_query($r->query()->save($r));
        }
        
        if($to == $jid)
            $evt->runEvent('myvcard');
            
        $evt->runEvent('vcard', $c);
    }
    
    public function load($key) {}
    public function save() {}
}
