<?php
function getip () {
    if ( isset($_SERVER["REMOTE_ADDR"]) )    { 
        return $_SERVER["REMOTE_ADDR"]; 
    } else if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    { 
        return $_SERVER["HTTP_X_FORWARDED_FOR"]; 
    } else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    { 
        return $_SERVER["HTTP_CLIENT_IP"]; 
    } 
}
?>
