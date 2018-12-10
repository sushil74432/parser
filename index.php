<?php 
	include_once("phpQuery-onefile.php");
    function curl_this_url( $url )
    {
        $user_agent='Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

        $options = array(

            CURLOPT_CUSTOMREQUEST   =>"GET",        //set request type post or get
            CURLOPT_POST            =>false,        //set to GET
            CURLOPT_USERAGENT       => $user_agent, //set user agent
            CURLOPT_SSL_VERIFYHOST  => 0,           //disale host verification
            CURLOPT_SSL_VERIFYPEER  => 0,           //disale SSL verification
            // CURLOPT_COOKIEFILE      =>"cookie.txt", //set cookie file
            // CURLOPT_COOKIEJAR       =>"cookie.txt", //set cookie jar
            CURLOPT_RETURNTRANSFER  => true,     // return web page
            CURLOPT_HEADER          => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION  => true,     // follow redirects
            //CURLOPT_ENCODING        => "",       // handle all encodings
            CURLOPT_AUTOREFERER     => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT  => 120,      // timeout on connect
            CURLOPT_CONNECTTIMEOUT  => 120,      // timeout on response
            CURLOPT_MAXREDIRS       => 10,       // stop after 10 redirects
        );

        $ch      = curl_init( $url );
        curl_setopt_array( $ch, $options );
        $content = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );
        curl_close( $ch );

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;
        return $content;
    }

    $response = curl_this_url("https://find-an-architect.architecture.com/FAAPractices.aspx?display=400");
    print_r($response);
?>