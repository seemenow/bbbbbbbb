<?php

namespace moxl;

/*
 * The presence builder
 */
function presenceMaker($to = false, $status = false, $show = false, $priority = 0, $type = false)
{
    global $session;
    $toxml = $typexml = $statusxml = $showxml = $priorityxml = '';

    if($to != false)
        $toxml = 'to="'.str_replace(' ', '\40', $to).'"';
    if($type != false)
        $typexml = 'type="'.$type.'"';
    if($status != false)
        $statusxml = '<status>'.$status.'</status>';
    if($show != false)
        $showxml = '<show>'.$show.'</show>';
    if($priority != 0)
        $priorityxml = '<priority>'.$priority.'</priority>';

    /*
            */
    return '
        <presence '.$toxml.' xmlns="jabber:client" from="'.$session['user'].'@'.$session['host'].'/'.$session['ressource'].'" '.$typexml.' id="'.$session['id'].'">
            '.$statusxml.'
            '.$showxml.'
            '.$priorityxml.'
            <c xmlns="http://jabber.org/protocol/caps"
               hash="sha-1"
               node="http://moxl.movim.eu/"
               ver="15eea36e40b03533a8ede69b8667c43edc351b4a" />
        </presence>';
}

/*
 * Simple presence without parameters
 */
function presenceSimple()
{
    $xml = presenceMaker(false, false, false, false, false);
    request($xml);
}

/*
 * Subscribe to someone presence
 */
function presenceUnavaiable()
{
    $xml = presenceMaker(false, false, false, false, 'unavaiable');
    request($xml, 'terminate');
}

/*
 * Subscribe to someone presence
 */
function presenceSubscribe($to, $status)
{
    $xml = presenceMaker($to, $status, false, false, 'subscribe');
    request($xml);
}

/*
 * Unsubscribe to someone presence
 */
function presenceUnsubscribe($to, $status)
{
    $xml =  presenceMaker($to, $status, false, false, 'unsubscribe');
    request($xml);
}

/*
 * Accept someone presence request
 */
function presenceSubscribed($to)
{
    $xml = presenceMaker($to, false, false, false, 'subscribed');
    request($xml);
}

/*
 * Refuse someone presence request
 */
function presenceUnsubscribed($to)
{
    $xml =  presenceMaker($to, false, false, false, 'unsubscribed');
    request($xml);
}

/*
 * Go away
 */
function presenceAway($status)
{
    $xml =  presenceMaker(false, $status, 'away', false, false);
    request($xml);
}

/*
 * Go chatting
 */
function presenceChat($status)
{
    $xml =  presenceMaker(false, $status, 'chat', false, false);
    request($xml);
}

/*
 * Do not disturb
 */
function presenceDND($status)
{
    $xml = presenceMaker(false, $status, 'dnd', false, false);
    request($xml);
}

/*
 * eXtended Away
 */
function presenceXA($status)
{
    $xml =  presenceMaker(false, $status, 'xa', false, false);
    request($xml);
}
