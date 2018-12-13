<?php 
	include_once("phpQuery-onefile.php");
	$page_no = 50;
    $entry_count = 0;

    function curl_this_url($url) {
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


    function data_scraper($page_no) {
        global $entry_count;
        $phone_present = 0; //flag to check if phone number is available in listing
        $email_present = 0; //flag to check if email is available in listing
    	$url = "https://find-an-architect.architecture.com/FAAPractices.aspx?display=".$page_no;
    	$data_array = array(); //Contains the arrays of all required data.
        $dom = curl_this_url($url);
    	$document = phpQuery::newDocumentHTML($dom, $charset = 'utf-8');
        if($entry_count == 0) {
            //echo "counting total entries";
            $entry_count = pq("input#faaCount")->val();
        }
        
        /**
            Scrapes the Names of the firm from given html block.
        **/
    	$firm_name = $document[".listingItem-details > h3 > a"];
        $firm_name_array = array();
        foreach ($firm_name as $firm) {
            $firm = pq($firm);
            $name = $firm->html();
            $firm_name_array[] = $name;
        }

        /**
            This Block scrapes the Address of the firm from given html block. This further splits the address into address 1 and address 2.
        **/
        $firm_address = $document[".listingItem-details > .pageMeta > .pageMeta-col > .address"];
        //$firm_address_array = array();
        foreach ($firm_address as $firm) {
            $firm = pq($firm);
            $address = $firm->html();
            $firm_address_array[] = $address;
        }
        $address = array();
        foreach ($firm_address_array as $addr) {
            $addr_buffer = explode(",",$addr);  //splits the address on presence of , 
            array_push($address,split_address($addr_buffer)); //2d array with address1 and address2 for each firm index as key
            //echo "</br>";
            //var_dump($address);
        }
        //var_dump($address);


        /**
            This Block scrapes the Phone of the firm from given html block.
        **/
        $firm_phone = $document[".listingItem-details > .pageMeta:nth-child(2) > .pageMeta-col:nth-child(2) > .pageMeta-item:first-child"];
        $firm_phone_array = array();
        foreach ($firm_phone as $firm) {
            $firm = pq($firm);
            $phone = $firm->html();
            //var_dump($phone);die();
            if(strpos($phone, "Tel: ") !== false) {
                $phone = str_replace("Tel: ", "", $phone);
                $phone_present = 1;
            } else {
                $phone = "NA";
                $phone_present = 0;
            }
            $firm_phone_array[] = $phone;
            //scrape_email();
        }            
        //var_dump($firm_phone_array);


        /**
            This Block scrapes the email of the firm from given html block.
        **/
        // function scrape_email(){
        // if ($phone_present) {
        //     $firm_email = $document[".listingItem-details > .pageMeta:nth-child(2) > .pageMeta-col:nth-child(2) > .pageMeta-item:nth-child(2)>.faaemail"];
        // } else{
        //     $firm_email = $document[".listingItem-details > .pageMeta:nth-child(2) > .pageMeta-col:nth-child(2) > .pageMeta-item:first-child > .faaemail"];
        // }
        // $firm_email_array = array();
        //     foreach ($firm_email as $firm) {
        //         $firm = pq($firm);
        //         $email = $firm->html();
        //         //var_dump($email);die();
        //         if(strpos($email, "@") !== false) {
        //             $email_present = 1;
        //         } else {
        //             $email = "NA";
        //             $email_present = 0;
        //         }
        //         $firm_email_array[] = $email;
        //     }
        //     var_dump($firm_email_array);
        // }




        // $firm_email = $document[".listingItem-details > .pageMeta:nth-child(2) > .pageMeta-col:nth-child(2) > .pageMeta-item:nth-child(2)"];
        // $firm_email_array = array();
        // $email_pattern = '%<a[^>]+class="spell"[^>]*>(.*?)</a>%';
        // foreach ($firm_email as $firm) {
        //     $firm = pq($firm);
        //     $email_block = $firm->html();            
        //     preg_match_all($email_pattern, $email_block, $email);
        //     echo 1;
        // }
        // var_dump($email);
             

            // if (strpos($email, "faaemail")!== false || strpos($email, "mailto:")!== false) {
            //     $firm_email_address = $document[".listingItem-details > .pageMeta:nth-child(2) > .pageMeta-col:nth-child(2) > .pageMeta-item:nth-child(2) > .faaemail"];
            //     $firm_email_array = array();
            //     foreach ($firm_email as $firm) {
            //         $firm = pq($firm);
            //         $email = $firm->html();
            //         $firm_email_array[] = $email;
            //     }
            //     $firm_email_array[] = $email;
            //     echo "in 1 </br>";
            // }
            // else if(strpos($email, "exLink")!== false || strpos($email, "_blank")!== false) {
            //     $firm_email = $document[".listingItem-details > .pageMeta:nth-child(2) > .pageMeta-col:nth-child(2) > .pageMeta-item:first-child"];
            //     $firm_email_array = array();
            //     foreach ($firm_email as $firm) {
            //         $firm = pq($firm);
            //         $email = $firm->html();
            //         $firm_email_array[] = $email;
            //     }
            //     $firm_email_array[] = $email;
            //     echo "in 2 </br>";
            // }
            // else{
            //     $firm_email_array[] = "NA";
            //     echo "in 3 </br>";
            // }
            
        
        //var_dump($firm_email_array);die();


    }

    /**
        Takes the whole address as a comma seperated string and splits into address1 and address2.
        Returns aggregared array 'address' where address[0] is address1 and address[1] is address2. 
    **/
    function split_address($addr){
        //echo "split address called";
        $arr_size = sizeof($addr);
        $address = array();
        for ($count=0; $count < ($arr_size-2); $count++) { 
            $addr_1[] = $addr[$count];
            $count++;
        }
        for ($count=($arr_size-1); $count < $arr_size; $count++) { 
            $addr_2[] = $addr[$count]; 
            $count++;
        }
        // $addr_1 = array_map('strval', $addr_1);
        // $addr_2 = array_map('strval', $addr_2);;
        array_push($address, $addr_1[0]);
        array_push($address, $addr_2[0]);
        return $address;
    }

    data_scraper(50);
    echo $entry_count;
    //data_scraper(50);
    // $response = curl_this_url("https://find-an-architect.architecture.com/FAAPractices.aspx?display=4000");
    // print_r($response);
?>