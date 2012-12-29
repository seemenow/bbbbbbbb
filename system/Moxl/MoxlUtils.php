<?php
/**
 * @file MoxlUtils.php
 *
 * @brief Some stuff for Moxl
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

// A simple function which clean and reindent an XML string
function cleanXML($xml) {
    if($xml != '') {
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        $doc->formatOutput = true;
        return $doc->saveXML();
    } else {
        return '';
    }
}

// Display RAW XML in the browser
function displayXML($xml) {
    echo '<pre>'.htmlentities(cleanXML($xml)).'</pre>';
}

function explodeData($data) {
    $data = explode(',', $data);
    $pairs = array();
    $key = false;
    
    foreach($data as $pair) {
        $dd = strpos($pair, '=');
        if($dd) {
            $key = trim(substr($pair, 0, $dd));
            $pairs[$key] = trim(trim(substr($pair, $dd + 1)), '"');
        }
        else if(strpos(strrev(trim($pair)), '"') === 0 && $key) {
            $pairs[$key] .= ',' . trim(trim($pair), '"');
            continue;
        }
    }
    
    return $pairs;
}

function implodeData($data) {
    $return = array();
    foreach($data as $key => $value)
        $return[] = $key . '="' . $value . '"';
    return implode(',', $return);
}

function encryptPassword($data, $user, $pass) {
    foreach(array('realm', 'cnonce', 'digest-uri') as $key)
        if(!isset($data[$key]))
            $data[$key] = '';
    
    $pack = md5($user.':'.$data['realm'].':'.$pass);
    
    if(isset($data['authzid'])) 
        $a1 = pack('H32',$pack).sprintf(':%s:%s:%s',$data['nonce'],$data['cnonce'],$data['authzid']);
    else 
        $a1 = pack('H32',$pack).sprintf(':%s:%s',$data['nonce'],$data['cnonce']);
    $a2 = 'AUTHENTICATE:'.$data['digest-uri'];
    
    return md5(sprintf('%s:%s:%s:%s:%s:%s', md5($a1), $data['nonce'], $data['nc'], $data['cnonce'], $data['qop'], md5($a2)));
}

function generateNonce() {
    $str = '';
    mt_srand((double) microtime()*10000000);
    for($i=0; $i<32; $i++)
        $str .= chr(mt_rand(0, 255));
    return $str;
}

function pbkdf2($data, $secret, $iteration, $dkLen=32, $algo='sha1') {
    $hLen = strlen(hash($algo, null, true));
    
    $l = ceil($dkLen/$hLen);
    $t = null;
    for($i=1; $i<=$l; $i++) {
        $f = $u = hash_hmac($algo, $s.pack('N', $i), $p, true);
        for($j=1; $j<$c; $j++)
            $f ^= ($u = hash_hmac($algo, $u, $p, true));
        $t .= $f;
    }
    return substr($t, 0, $dk_len);
}
