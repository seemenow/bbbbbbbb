<?php
/*
 * VcardSet.php
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

class VcardSet extends XECAction
{
    private $_data;
    
    public function request() 
    {
        $this->store();
        vcardSet($this->_data);
    }
    
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }
    
    public function handle($stanza) {
        $evt = new \Event();
        $evt->runEvent('myvcardvalid');
    }
    
    public function errorFeatureNotImplemented($stanza) {
        $evt = new \Event();
        $evt->runEvent('myvcardinvalid', 'vcardfeaturenotimpl');
    }
    
    public function errorBadRequest($stanza) {
        $evt = new \Event();
        $evt->runEvent('myvcardinvalid', 'vcardbadrequest');
    }
}
