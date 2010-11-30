<?php
/* Please use \r\n for newlines in body_text and body_email */
/* filename_this is the actual system filename on the server - filename_display is what the receiver will download it as */

function send_html_mail ($fromname, $fromemail, $toname, $toemail, $subject, $body_text, $body_html, $file_mimetype, $filename_this, $filename_display) {
    //create a boundary string. It must be unique 
    //so we use the MD5 algorithm to generate a random hash 
    $random_hash = md5(date('r', time())); 
    //define the headers we want passed. Note that they are separated with \r\n 
    $headers = "From: ".$fromname." <".$fromemail.">\r\nReply-To: ".$fromname." <".$fromemail.">"; 
    //add boundary string and mime type specification 
    $headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"\r\n\r\n"; 
    //read the atachment file contents into a string,
    //encode it with MIME base64,
    //and split it into smaller chunks
    $attachment = chunk_split(base64_encode(file_get_contents($filename))); 
    //define the body of the message. 
    $message = "--PHP-mixed-".$random_hash."\r\n";
    $message .= "Content-Type: multipart/alternative; boundary=\"PHP-alt-".$random_hash."\"\r\n\r\n";
    $message .= "--PHP-alt-".$random_hash."\r\n";
    $message .= "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $body_text;
    $message .= "--PHP-alt-".$random_hash."\r\n";
    $message .= "Content-Type: text/html; charset=\"iso-8859-1\"\r\n"; 
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $body_html;
    $message .= "--PHP-alt-".$random_hash."--\r\n\r\n"; 
    $message .= "--PHP-mixed-".$random_hash."\r\n";
    $message .= "Content-Type: ".$file_mimetype."; name=\"".$filename_display."\"\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n";
    $message .= "Content-Disposition: attachment\r\n\r\n";
    $message .= $attachment."\n"; 
    $message .= "--PHP-mixed-".$random_hash."--\r\n"; 

    if (!(@mail( $toemail, $subject, $message, $headers ))) {
        return FALSE;
    } else {
        return TRUE;
    }
}
?>
