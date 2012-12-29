<!DOCTYPE html>
<html>
<head>
<script type="text/javascript">
function requestMoxl(url)
{
    var xmlhttp;
    
    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    
    xmlhttp.onreadystatechange=function()
    {
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
        {
            document.getElementById("myDiv").innerHTML=xmlhttp.responseText+document.getElementById("myDiv").innerHTML;
            ping();
        }
    }
    xmlhttp.open("GET",url,true);
    xmlhttp.send();
}

function clearLog()
{
    document.getElementById("myDiv").innerHTML= "";
}

function login()
{
    requestMoxl("Moxl.php?r=login");
}

function logout()
{
    requestMoxl("Moxl.php?r=logout");
}

function ping()
{
    requestMoxl("Moxl.php?r=ping");
}

function message()
{
    requestMoxl("Moxl.php?r=message");
}

function chat()
{
    requestMoxl("Moxl.php?r=chat");
}

function dnd()
{
    requestMoxl("Moxl.php?r=dnd");
}

function rostergetlist()
{
    requestMoxl("Moxl.php?r=rgl");
}

function rostergetitem()
{
    requestMoxl("Moxl.php?r=rgi");
}
function rosterremoveitem()
{
    requestMoxl("Moxl.php?r=rri");
}
</script>
</head>
<body style="margin: 0px; padding: 0px;">

<div id="myDiv" style="height: 600px; width: 100%; background-color: black; color: white; overflow: scroll;"></div>
<button type="button" onclick="message();">Send Message</button>
<button type="button" onclick="login();">Connect</button>
<button type="button" onclick="logout();">Logout</button>
<button type="button" onclick="chat();">Chat</button>
<button type="button" onclick="dnd();">DND</button>
<button type="button" onclick="rostergetlist();">Roster Get List</button>
<button type="button" onclick="rostergetitem();">Roster Update Item</button>
<button type="button" onclick="rosterremoveitem();">Roster Remove Item</button>
<br />
<button type="button" onclick="clearLog();">Clear log</button>
</body>
</html>

