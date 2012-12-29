<?

namespace moxl;

require('loader.php');

// We set the default timezone to the server timezone
date_default_timezone_set('UTC');

$sess = Session::start(APP_NAME);

$s = $sess->get('session');

//global $session;

if($s != false) {
    $session = $sess->get('session');
}
else {
    $session = array(
            'rid' => 1,
            'sid' => 0,
            'id'  => 0,
            'url' => 'localhost:5280/http-bind',
            'port'=> 5222,
            'host'=> 'movim.eu',
            'domain' => 'etenil.thruhere.net',
            'ressource' => 'moxl', 
            
            'user'     => 'user',
            'password' => 'pass');
}

if($_GET['r'] == 'login')
    login();
elseif($_GET['r'] == 'ping')
    ping();
elseif($_GET['r'] == 'message')
    message("user@gmail.com", "gna");
elseif($_GET['r'] == 'chat') {
    $p = new PresenceChat();
    $p->request();
}
elseif($_GET['r'] == 'logout') {
    $p = new PresenceUnavaiable();
    $p->request();
}
elseif($_GET['r'] == 'dnd') {
    $p = new PresenceDND();
    $p->request();
}
elseif($_GET['r'] == 'rgi') {
    $ro = new RosterUpdateItem();
    $ro
        ->setTo('edhelas@gmail.com')
        ->setName('Timothée')
        ->setGroup('')
        ->request();
}
elseif($_GET['r'] == 'rri') {;
    $ro = new RosterRemoveItem();
    $ro
        ->setTo('edhelas@gmail2.com')
        ->request();
}
elseif($_GET['r'] == 'rgl') {
    $ro = new RosterGetList();
    $ro->request();
}

    

$sess->set('session', $session);
