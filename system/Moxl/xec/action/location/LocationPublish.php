<?php
/*
 * LocationPublish.php
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

class LocationPublish extends XECAction
{
    private $_to;
    private $_geo;
    
    public function request() 
    {
        $this->store();
        locationPublish($this->_to, $this->_geo);
    }
    
    public function setTo($to)
    {
        $this->_to = $to;
        return $this;
    }

    public function setGeo($geo)
    {
        $this->_geo = $geo;
        return $this;
    }
        
    public function handle($stanza) {
        $evt = new \Event();
        
        $from = current(explode('/',(string)$stanza->attributes()->from));
        
        $query = \Contact::query()
                        ->select()
                        ->where(
                            array('jid' => $from)
                        );
        $c = \Contact::run_query($query);
        
        $c = $c[0];
        
        $c->loclatitude->setval($this->_geo['latitude']);
        $c->loclongitude->setval($this->_geo['longitude']);
        $c->localtitude->setval($this->_geo['altitude']);
        $c->loccountry->setval($this->_geo['country']);
        $c->loccountrycode->setval($this->_geo['countrycode']);
        $c->locregion->setval($this->_geo['region']);
        $c->locpostalcode->setval($this->_geo['postalcode']);
        $c->loclocality->setval($this->_geo['locality']);
        $c->locstreet->setval($this->_geo['street']);
        $c->locbuilding->setval($this->_geo['building']);
        $c->loctext->setval($this->_geo['text']);
        $c->locuri->setval($this->_geo['uri']);
        $c->loctimestamp->setval(date(
                            'Y-m-d H:i:s', 
                            time()));

        $c->run_query($c->query()->save($c));
        
        $evt->runEvent('locationpublished', $c);
    }
    
    public function errorFeatureNotImplemented($stanza) {
        $evt = new \Event();
        $evt->runEvent('locationpublisherror', t("Your server doesn't support location publication"));
    }
    
    public function errorNotAuthorized($stanza) {
        $evt = new \Event();
        $evt->runEvent('locationpublisherror', t("Your are not authorized to publish your location"));
    }
    
    public function errorServiceUnavailable($stanza) {
        $this->errorFeatureNotImplemented($stanza);
    }
    
    public function errorForbidden($stanza) {
        $this->errorNotAuthorized($stanza);
    }

}
