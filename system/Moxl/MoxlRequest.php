<?php
/**
 * @file MoxlRequest.php
 *
 * @brief Build and fire the XML requests to the BOSH module of the
 * XMPP server.
 *
 * Copyright © 2012 Timothée Jaussoin
 *
 * This file is part of Moxl.
 *
 * Moxl is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * Moxl is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Datajar.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace moxl;

class MoxlRequest {
    // XMPP attributes
    private $_rid;
    private $_sid;
    private $_id;
    private $_xml;

    // Curl attributes
    private $_url;

    // System attributes
    private $_callback;

    // Proxy attributes
    private $_proxyenabled;
    private $_proxyurl;
    private $_proxyport;
    private $_proxyuser;
    private $_proxypass;

    public function __construct($xml = false)
    {
        // We load the session in the internal attributes
        global $session;
        $this->_rid = $session['rid'];
        $this->_sid = $session['sid'];
        $this->_id  = $session['id'];
        $this->_url = $session['url'];

        $this->_proxyenabled = $session['proxyenabled'];
        $this->_proxyurl = $session['proxyurl'];
        $this->_proxyport = $session['proxyport'];
        $this->_proxyuser = $session['proxyuser'];
        $this->_proxypass = $session['proxypass'];

        // We set the XML
        if($xml != false)
            $this->_xml = $xml;

        // And we save the session
        $session['rid']++;
        $session['id']++;

        $sess = \Session::start(APP_NAME);
        $sess->set('session', $session);
    }


    public function setXML($xml)
    {
        // Load and save the XML using DOMDocument to clean it
        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $xml = $doc->saveXML();
        $this->_xml = $xml;

        return $this;
    }


    public function fire()
    {
        MoxlLogger::log(
            $this->_url."\n"
            .">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n"
            .cleanXML($this->_xml)
            .">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>", LOG_DEBUG);

        $ch = curl_init($this->_url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_xml);

        // Little hack to fix a return error on the Squid proxy Daemon
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

        if($this->_proxyenabled) {
            curl_setopt($ch, CURLOPT_PROXY, $this->_proxyurl);
            curl_setopt($ch, CURLOPT_PROXYPORT, $this->_proxyport);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->_proxyuser.":".$this->_proxypass);

        }

        // Fire !
        $rs = array();

        $content = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", curl_exec($ch));

        $rs['content'] = $content;
        $rs['errno'] = curl_errno($ch);
        $rs['errmsg'] = curl_error($ch);
        $rs['header'] = curl_getinfo($ch);
        if($rs['content'] != false && $rs['content'] != '') {
            MoxlLogger::log(
                "<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<\n"
                .cleanXML($rs['content'])."\n"
                ."<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<", LOG_DEBUG);
        }
        /*
         * If an error occur during the request, we log it and stop the
         * ping loop
         */
        elseif($rs['errno'] != 0 || $rs['content'] == '') {
            MoxlLogger::log(
                "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n"
                ."Error n°".$rs['errno']." - ".$rs['errmsg']."\n"
                ."!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!", LOG_ERR);
                if($rs['errno'] == 7 || $rs['errno'] == 0)
                    http_response_code('400');
            return 'bosherror';
        }
        curl_close($ch);

        return $rs;
    }
}
