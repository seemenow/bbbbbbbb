<?php
/*
 * Basic stanza for the XEP-0049 implementation
 */ 

namespace moxl;

function storageSet($xmlns, $data)
{
    $xml = '
        <query xmlns="jabber:iq:private">
            <data xmlns="'.$xmlns.'">
                '.$data.'
            </data>
        </query>';
    $xml = iqWrapper($xml, false, 'set');
    request($xml);
}

function storageGet($xmlns)
{
    $xml = '
        <query xmlns="jabber:iq:private">
            <data xmlns="'.$xmlns.'"/>
        </query>';
    $xml = iqWrapper($xml, false, 'get');
    request($xml);
}
