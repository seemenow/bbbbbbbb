<?php

namespace moxl;

function discoAnswer($to)
{
    global $session;
    $xml = '
        <iq type="result" to="'.$to.'">
           <feature var="urn:xmpp:microblog:0"/>
           <feature var="urn:xmpp:microblog:0+notify"/>
           <feature var="urn:xmpp:inbox"/>
           <feature var="urn:xmpp:inbox+notify"/>
           <feature var="http://jabber.org/protocol/caps"/>
           <feature var="http://jabber.org/protocol/disco#info"/>
           <feature var="http://jabber.org/protocol/disco#items"/>
           <feature var="http://jabber.org/protocol/activity"/>
           <feature var="http://jabber.org/protocol/geoloc"/>
           <feature var="http://jabber.org/protocol/geoloc+notify"/>
           <feature var="http://jabber.org/protocol/http-bind"/>
           <feature var="http://jabber.org/protocol/pubsub"/>
           <feature var="http://jabber.org/protocol/tune"/>
           <feature var="http://jabber.org/protocol/tune+notify"/>
        </iq>';
    request($xml);
}
