<?php

namespace moxl;

function vcardGet($to)
{
    $xml = '<vCard xmlns="vcard-temp"/>';
    $xml = iqWrapper($xml, $to, 'get');
    request($xml);
}

function vcardSet($data)
{
    $xml = '
        <vCard xmlns="vcard-temp">
            <FN>'.$data['fn'].'</FN>
            <NICKNAME>'.$data['name'].'</NICKNAME>
            <URL>'.$data['url'].'</URL>
            <BDAY>'.$data['date'].'</BDAY>
            <EMAIL>
                <USERID>'.$data['email'].'</USERID>
            </EMAIL>
            <ADR>
                <LOCALITY>'.$data['locality'].'</LOCALITY>
                <PCODE>'.$data['postalcode'].'</PCODE>
                <CTRY>'.$data['country'].'</CTRY>
            </ADR>
            <DESC>
                '.$data['desc'].'
            </DESC>
            <X-GENDER>'.$data['gender'].'</X-GENDER>
            <MARITAL><STATUS>'.$data['marital'].'</STATUS></MARITAL>
            <PHOTO>
                <TYPE>'.$data['phototype'].'</TYPE>
                <BINVAL>'.$data['photobin'].'</BINVAL>
            </PHOTO>
        </vCard>';

    $xml = iqWrapper($xml, false, 'set');
    request($xml);
}
