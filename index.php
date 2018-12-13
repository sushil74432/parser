<?php 
    ini_set('error_reporting', E_ALL & ~E_WARNING & ~E_NOTICE);
	include_once("phpQuery-onefile.php");
    $entry_count = 0;

    /**
        cURLs an url passes to this function and returns the response header with content and errors if any.
    **/
    function curl_this_url( $url )
    {
        $user_agent='Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';

        $options = array(

            CURLOPT_CUSTOMREQUEST   =>"GET",        //set request type post or get
            CURLOPT_POST            =>false,        //set to GET
            CURLOPT_USERAGENT       => $user_agent, //set user agent
            CURLOPT_SSL_VERIFYHOST  => 0,           //disale host verification
            CURLOPT_SSL_VERIFYPEER  => 0,           //disale SSL verification
            CURLOPT_RETURNTRANSFER  => true,     // return web page
            CURLOPT_HEADER          => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION  => true,     // follow redirects
            CURLOPT_AUTOREFERER     => true,     // set referer on redirect
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
        return $header;
    }

    /**
        Consists the main scraping logic.
    **/

    function data_scraper($offset) {
        global $entry_count;
        $scraped_data = array();  //Contains the arrays of all required data.
        // $url = "file:///D:/xampp/htdocs/parser/parser/test_site/FAA Practices.html";
        $url = "https://find-an-architect.architecture.com/FAAPractices.aspx?display=500&page=".$offset;
        $curl_array = curl_this_url($url);
        $dom = $curl_array['content'];
        $document = phpQuery::newDocumentHTML($dom, $charset = 'utf-8');
        if($entry_count == 0) {
            $entry_count = pq("input#faaCount")->val();
            //echo $entry_count;
        }
        $article_node = $document["article"];
        $article_node_array = array();
        foreach ($article_node as $article) {
            $article = pq($article);
            $articles = $article->html();
            $article_node_array[] = $articles;
        }
        foreach ($article_node_array as $article) {
            $scraped_data_buffer = array();
            $document = phpQuery::newDocumentHTML($article);

            $firm_name = $document[".listingItem-details > h3 > a"];
            $firm_name_var = trim($firm_name->html());
            array_push($scraped_data_buffer, $firm_name_var);
            
            $firm_address = $document[".listingItem-details > .pageMeta > .pageMeta-col > .address"];
            $firm_address_var = $firm_address->html();
            $firm_address_array = explode(',',$firm_address_var);
            $firm_address_array = split_address($firm_address_array);
            array_push($scraped_data_buffer, trim($firm_address_array[0]));
            array_push($scraped_data_buffer, trim($firm_address_array[1]));

            $firm_phone = $document[".listingItem-details > .pageMeta:nth-child(2) > .pageMeta-col:nth-child(2) > .pageMeta-item:first-child"];
            $firm_phone_var = trim($firm_phone->html());
            $firm_phone_var = sanitize_phone($firm_phone_var);
            array_push($scraped_data_buffer, $firm_phone_var);

            $firm_website = $document[".exLink"];
            $firm_website_var = trim($firm_website->html());
            array_push($scraped_data_buffer, $firm_website_var);

            $firm_email = $document[".faaemail"];
            $firm_email_var = trim($firm_email->html());
            array_push($scraped_data_buffer, $firm_email_var);

            $firm_about = $document[".listingItem-extra > .pageMeta-item > p"];
            $firm_about_var = trim($firm_about->html());
            array_push($scraped_data_buffer, $firm_about_var);

            $firm_thumbnail = $document[".listingItem-thumbnail > img"];
            $firm_thumbnail_var = trim($firm_thumbnail->attr('src'));
            array_push($scraped_data_buffer, $firm_thumbnail_var);

            array_push($scraped_data, $scraped_data_buffer);
        }
        export_to_csv($scraped_data);
    }

    /**
        Takes the whole address as a comma seperated string and splits into address1 and address2.
        Returns aggregared array 'address' where address[0] is address1 and address[1] is address2. 
    **/
    function split_address($addr) {
        if(empty($addr)){
            $address = array(0 => array(0 => ''), 1 => array(0 => ''));
        } else{
            $arr_size = sizeof($addr);
            $address = array();
            for ($count=0; $count < ($arr_size-2); $count++) { 
                $addr_1[] = $addr[$count];
            }
            for ($count=($arr_size-2); $count < $arr_size; $count++) { 
                $addr_2[] = $addr[$count];
            }
            array_push($address, implode($addr_1));
            array_push($address, implode($addr_2));
        }
        return $address;
    }

    /**
        Checks the phone number and strips the extra string "Tel: "
    **/
    function sanitize_phone($phone) {
        if(strpos($phone, "Tel: ") !== false) {
            $phone = str_replace("Tel: ", "", $phone);
        } else {
            $phone = "";
        }
        return $phone;
    }

    /**
        takes an array as input and export it to CSV file architect_com_scraped_data.csv
    **/
    function export_to_csv($data){
        $fp = fopen("architect_com_scraped_data.csv", "a");
        foreach($data as $row){
                ///var_dump($row);die();
                fputcsv($fp, $row);
        }
        fclose($fp);        
    }
    
    /**
        Script starts here.
    **/

    $csv_header = array("Firm Name","Address 1","Address 2","Phone","Website","Email", "About", "Image URL");
    $csv_header_2d = array();
    array_push($csv_header_2d, $csv_header); //Converted into 2d array to make data structure compatible to generic function export_to_csv().
    export_to_csv($csv_header_2d);

    data_scraper(1);
    $offset = 2;
    $count = 500;
    while ($count <= $entry_count) {
        data_scraper($offset);
        $offset++;
        $count = $count + 500;
    }
?>