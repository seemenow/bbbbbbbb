<?php

namespace moxl;

function message($to, $content)
{
    global $session;
    $xml = '
        <message to="'.str_replace(' ', '\40', $to).'" type="chat" id="'.$session['id'].'">
            <body>'.$content.'</body>
            <request xmlns="urn:xmpp:receipts"/>
        </message>';
    request($xml);
}
