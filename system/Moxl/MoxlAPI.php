<?php

namespace moxl;

function createChallenge($decoded)
{
    global $session;
    $decoded = explodeData($decoded);

    if(!isset($decoded['digest-uri'])) $decoded['digest-uri'] = 'xmpp/'.$session['host'];

    $decoded['cnonce'] = base64_encode(generateNonce());

    if(isset($decoded['qop'])
    && $decoded['qop'] != 'auth'
    && strpos($decoded['qop'],'auth') !== false
    ) { $decoded['qop'] = 'auth'; }

    $response = array('username'=>$session['user'],
        'response' => encryptPassword(
                        array_merge(
                            $decoded,
                            array('nc'=>'00000001')),
                            $session['user'],
                            $session['password']),
        'charset' => 'utf-8',
        'nc' => '00000001',
        'qop' => 'auth'
    );

    foreach(array('nonce', 'digest-uri', 'realm', 'cnonce') as $key)
        if(isset($decoded[$key]))
            $response[$key] = $decoded[$key];

    $response = base64_encode(implodeData($response));

    return $response;
}

function boshWrapper($xml, $type = false)
{
    global $session;

    $typehtml = '';
    if($type != false)
        $typehtml = ' type="'.$type.'" ';

    return '
        <body
            rid="'.$session['rid'].'"
            sid="'.$session['sid'].'"
            '.$typehtml.'
            xmlns="http://jabber.org/protocol/httpbind">
            '.$xml.'
        </body>';
}

function iqWrapper($xml, $to = false, $type = false)
{
    global $session;
    $toxml = $typexml = '';
    if($to != false)
        $toxml = 'to="'.str_replace(' ', '\40', $to).'"';
    if($type != false)
        $typexml = 'type="'.$type.'"';

    return '
        <iq
            id="'.$session['id'].'"
            from="'.$session['user'].'@'.$session['host'].'/'.$session['ressource'].'"
            '.$toxml.'
            '.$typexml.'>
            '.$xml.'
        </iq>
    ';
}

function login()
{
    global $session;
    MoxlLogger::log("/// STREAM INIT");

        $xml = '
            <body
                content="text/xml; charset=utf-8"
                hold="1"
                xmlns="http://jabber.org/protocol/httpbind"
                wait="30"
                rid="'.$session['rid'].'"
                version="1.6"
                polling="0"
                secure="1"
                xmlns:xmpp="urn:xmpp:xbosh"
                to="'.$session['host'].'"
                route="xmpp:'.$session['domain'].':'.$session['port'].'"
                xmpp:version="1.0"
            />';

        $r = new MoxlRequest($xml);

        $xml = $r->fire();
        if($xml == 'bosherror') {
            return $xml;
        }

        $xmle = new \SimpleXMLElement($xml['content']);

        if($xmle->head || (string)$xmle->attributes()->type == 'terminate')
            return 'bosherror';

        $session['sid'] = (string)$xmle->attributes()->sid;

        $mec = (array)$xmle->streamfeatures->mechanisms;
        $mec = $mec['mechanism'];

        if(!is_array($mec))
            $mec = array($mec);

        /*if(in_array('SCRAM-SHA-1', $mec)) {
            MoxlLogger::log("/// MECANISM CHOICE DIGEST-MD5");
                $response = base64_encode(
                                'n,,n='.$session['user'].',r=d2fc512490a15036460b5489401439d6da5407fa');

                $xml = boshWrapper(
                        '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="SCRAM-SHA-1">
                            '.$response.'
                        </auth>');

                $r = new MoxlRequest($xml);
                $xml = $r->fire();

                $xmle = new \SimpleXMLElement($xml['content']);
                if($xmle->failure)
                    return 'errormechanism';

                $decoded = base64_decode((string)$xmle->challenge);
                \movim_log($decoded);
                $arr = explode(',', $decoded);
                \movim_log($arr);

                //pbkdf2($session['password'], $decoded['s'], $decoded['i']);

                //$response = base64_encode(
                //                'c=biws, '.$arr[1].',p=d2fc512490a15036460b5489401439d6da5407fa');

            exit;
        } else*/
        if(in_array('DIGEST-MD5', $mec)) {
            MoxlLogger::log("/// MECANISM CHOICE DIGEST-MD5");

                $xml = boshWrapper(
                        '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="DIGEST-MD5"/>');

                $r = new MoxlRequest($xml);
                $xml = $r->fire();

                $xmle = new \SimpleXMLElement($xml['content']);
                if($xmle->failure)
                    return 'errormechanism';

                $decoded = base64_decode((string)$xmle->challenge);

                if($decoded)
                    $response = createChallenge($decoded, $session);
                else
                    return 'errorchallenge';

            MoxlLogger::log("/// CHALLENGE");

                $xml = boshWrapper(
                        '<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl">
                            '.$response.'
                        </response>');

                $r = new MoxlRequest($xml);
                $xml = $r->fire();

                $xmle = new \SimpleXMLElement($xml['content']);
                if($xmle->failure)
                    return 'wrongaccount';

            MoxlLogger::log("/// RESPONSE");

                $xml = boshWrapper(
                        '<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl"/>');

                $r = new MoxlRequest($xml);
                $xml = $r->fire();

            MoxlLogger::log("/// RESTART REQUEST");

                $xml = '
                    <body
                    rid="'.$session['rid'].'"
                    sid="'.$session['sid'].'"
                    xmlns="http://jabber.org/protocol/httpbind"
                    to="'.$session['host'].'"
                    xmpp:restart="true"
                    xmlns:xmpp="urn:xmpp:xbosh"/>';

                $r = new MoxlRequest($xml);
                $xml = $r->fire();

            MoxlLogger::log("/// RESSOURCE BINDING REQUEST");

                $xml = boshWrapper(
                    '<iq type="set" id="'.$session['id'].'">
                        <bind xmlns="urn:ietf:params:xml:ns:xmpp-bind">
                            <resource>'.$session['ressource'].'</resource>
                        </bind>
                    </iq>');

                $r = new MoxlRequest($xml);
                $xml = $r->fire();

                $xmle = new \SimpleXMLElement($xml['content']);

                if($xmle->head || (string)$xmle->attributes()->type == 'terminate')
                    return 'failauth';

                elseif($xmle->iq->bind->jid) {
                    list($jid, $ressource) = explode('/', (string)$xmle->iq->bind->jid);
                    if($ressource)
                        $session['ressource'] = $ressource;
                }

        } elseif(in_array('PLAIN', $mec)) {
            MoxlLogger::log("/// MECANISM CHOICE PLAIN");
                $response = base64_encode(chr(0).$session['user'].chr(0).$session['password']);

                $xml = boshWrapper(
                        '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="PLAIN" client-uses-full-bind-result="true">'.
                            $response.
                        '</auth>');

                $r = new MoxlRequest($xml);
                $xml = $r->fire();

                $xmle = new \SimpleXMLElement($xml['content']);

                if($xmle->failure)
                    return 'wrongaccount';


                $xml = boshWrapper('');

                $r = new MoxlRequest($xml);
                $xml = $r->fire();

            MoxlLogger::log("/// BIND REQUEST");

                $xml = boshWrapper(
                    '<iq type="set" id="'.$session['id'].'">
                        <bind xmlns="urn:ietf:params:xml:ns:xmpp-bind"/>
                    </iq>');

                $r = new MoxlRequest($xml);
                $xml = $r->fire();

                $xmle_res = new \SimpleXMLElement($xml['content']);

                list($jid, $ressource) = explode('/', (string)$xmle_res->iq->bind->jid);
                if($ressource)
                    $session['ressource'] = $ressource;


        } else {
            return 'errormechanism';
        }

    MoxlLogger::log("/// START THE SESSION");

        $xml = boshWrapper(
            '<iq
                type="set"
                id="'.$session['id'].'"
                to="'.$session['host'].'">
                <session xmlns="urn:ietf:params:xml:ns:xmpp-session"/>
            </iq>');

        $r = new MoxlRequest($xml);
        $xml = $r->fire();

    MoxlLogger::log("/// AUTH SUCCESSFULL");

    $session['on'] = true;
    unset($session['password']);
    $sess = \Session::start(APP_NAME);
    $sess->set('session', $session);

    // We get the general configuration

    $s = new StorageGet();
    $s->setXmlns('movim:prefs')
      ->request();

    // We grab the precedente presence from the Cache and send it !
    $presence = \Cache::c('presence');

    if(!isset($presence['show']) || $presence['show'] == '')
        $presence['show'] = 'chat';

    if(!isset($presence['status']) || $presence['status'] == '')
        $presence['status'] = 'Online with Moxl';

    switch($presence['show']) {
        case 'chat':
            $p = new PresenceChat();
            $p->setStatus(htmlspecialchars($presence['status']))->request();
            break;
        case 'away':
            $p = new PresenceAway();
            $p->setStatus(htmlspecialchars($presence['status']))->request();
            break;
        case 'dnd':
            $p = new PresenceDND();
            $p->setStatus(htmlspecialchars($presence['status']))->request();
            break;
        case 'xa':
            $p = new PresenceXA();
            $p->setStatus(htmlspecialchars($presence['status']))->request();
            break;
    }

    // Here we go !!!
    return "OK";
}

/*
 *  Call the request class with the correct XML
 */
function request($xml, $type = false)
{
    global $session;
    if($session['on'] == true) {
        $xml = boshWrapper($xml, $type);

        $r = new MoxlRequest($xml);
        $xmlr = $r->fire();

        handle($xmlr);

    } else {
        MoxlLogger::log(
            "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n"
            ."Session unstarted, please login\n"
            ."!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
        http_response_code('400');
        exit;
    }
}

/*
 * A simple ping to the XMPP BOSH
 */
function ping()
{
    global $session;

    if($session['on'] == true) {
        $xml = boshWrapper(
            '');

        $r = new MoxlRequest($xml);
        $xmlr = $r->fire();

        handle($xmlr);
    } else {
        MoxlLogger::log(
            "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!\n"
            ."Session unstarted, please login\n"
            ."!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");
        http_response_code('400');
        exit;
    }
}

/*
 * Handle each request and send it to XEC
 */
function handle($callback)
{
    if($callback['content'] != '' && isset($callback['content'])) {
        // Convert it to a SimpleXMLElement
        $xmle = new \SimpleXMLElement($callback['content']);
        XECHandler::handle($xmle);
    }

    $evt = new \Event();
    $evt->runEvent('incomingemptybody', 'ping');
}
