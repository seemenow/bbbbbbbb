<?php

$base = __DIR__.'/';

define('XMPP_LIB_NAME', 'moxl');

require_once($base.'MoxlLogger.php');
require_once($base.'MoxlAPI.php');
require_once($base.'MoxlRequest.php');
require_once($base.'MoxlUtils.php');

require_once($base.'stanza/base/Message.php');
require_once($base.'stanza/base/Presence.php');
require_once($base.'stanza/base/Roster.php');
require_once($base.'stanza/base/Vcard.php');

require_once($base.'stanza/microblog/Microblog.php');

require_once($base.'stanza/notification/Notification.php');

require_once($base.'stanza/storage/Storage.php');

require_once($base.'stanza/disco/Disco.php');

require_once($base.'stanza/location/Location.php');

// XEC loader

require_once($base.'xec/XECAction.php');
require_once($base.'xec/XECPayload.php');

require_once($base.'xec/action/roster/RosterGetList.php');
require_once($base.'xec/action/roster/RosterAddItem.php');
require_once($base.'xec/action/roster/RosterUpdateItem.php');
require_once($base.'xec/action/roster/RosterRemoveItem.php');

require_once($base.'xec/action/presence/PresenceAway.php');
require_once($base.'xec/action/presence/PresenceChat.php');
require_once($base.'xec/action/presence/PresenceDND.php');
require_once($base.'xec/action/presence/PresenceSubscribe.php');
require_once($base.'xec/action/presence/PresenceSubscribed.php');
require_once($base.'xec/action/presence/PresenceUnavaiable.php');
require_once($base.'xec/action/presence/PresenceUnsubscribe.php');
require_once($base.'xec/action/presence/PresenceUnsubscribed.php');
require_once($base.'xec/action/presence/PresenceXA.php');

require_once($base.'xec/action/vcard/VcardGet.php');
require_once($base.'xec/action/vcard/VcardSet.php');

require_once($base.'xec/action/microblog/MicroblogGet.php');
require_once($base.'xec/action/microblog/MicroblogCreateNode.php');
require_once($base.'xec/action/microblog/MicroblogCommentsGet.php');
require_once($base.'xec/action/microblog/MicroblogPostPublish.php');
require_once($base.'xec/action/microblog/MicroblogPostDelete.php');
require_once($base.'xec/action/microblog/MicroblogCommentPublish.php');
require_once($base.'xec/action/microblog/MicroblogCommentCreateNode.php');

require_once($base.'xec/action/notification/NotificationGet.php');

require_once($base.'xec/action/storage/StorageGet.php');
require_once($base.'xec/action/storage/StorageSet.php');

require_once($base.'xec/action/location/LocationPublish.php');

require_once($base.'xec/XECHandler.php');
