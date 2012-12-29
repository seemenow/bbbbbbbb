<?php

namespace moxl;

/*
 * The roster builder
 */
function rosterBuilder($xml, $to, $type)
{  
    $xml = '
        <query xmlns="jabber:iq:roster">
            '.$xml.'
        </query>';
        
    $xml = iqWrapper($xml, $to, $type);
    request($xml);
}

function rosterGet()
{
    $xml = '<query xmlns="jabber:iq:roster"/>';
    
    $xml = iqWrapper($xml, false, 'get');
    request($xml);
}

/*
 * Add contact
 */
function rosterAdd($to, $name, $group)
{
    $xml ='
        <item
            jid="'.$to.'"
            name="'.$name.'">
            <group>'.$group.'</group>
        </item>';
    
    $xml = rosterBuilder($xml, false, 'set');
    request($xml);
}

function rosterUpdate($to, $name, $group)
{
    $xml = rosterAdd($to, $name, $group);
    request($xml);
}

/*
 * Remove a contact
 */
function rosterRemove($to)
{
    $xml = '
        <item jid="'.$to.'" subscription="remove"/>';
        
    $xml = rosterBuilder($xml, false, 'set');
    request($xml);
}
