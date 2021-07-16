<?php

namespace curltools;

/**
 * Class CurlTools
 * @package CurlTools
 */
class CurlTools{
    public static function Http_Post($url,$header= array(),$data = array()){
        $ch = curl_init();

        if(PROJ_ENV === "dev")//for Fiddler debug
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);




        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $sResult = curl_exec($ch);
        if($sError=curl_error($ch)){
            error_log($sError);
            return $sError;
            die();
        }
        curl_close($ch);
        return $sResult;
    }

    public static function Http_Get($url,$header= array()){
        $ch = curl_init();

        if(PROJ_ENV === "dev")//for Fiddler debug
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT , 60);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);

        $sResult = curl_exec($ch);
        if($sError=curl_error($ch)){
            error_log($sError);
            return $sError;
            die();
        }
        curl_close($ch);
        return $sResult;
    }

    public static function Http_GetHeader($url,$header=array()){
        $ch = curl_init();

        if(PROJ_ENV === "dev")//for Fiddler debug
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
        }

        // set url
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //enable headers
        curl_setopt($ch, CURLOPT_HEADER, 1);

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT , 60);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        //get only headers
        // curl_setopt($ch, CURLOPT_NOBODY, 1);





        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
        // $output contains the output string
        $output = curl_exec($ch);

        $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);

        // close curl resource to free up system resources
        curl_close($ch);

        $headers = [];
        $output = rtrim($output);
        $data = explode("\n",$output);
        $headers['code'] = $code;
        $headers['status'] = $data[0];
        array_shift($data);

        foreach($data as $part){

            //some headers will contain ":" character (Location for example), and the part after ":" will be lost, Thanks to @Emanuele
            $middle = explode(":",$part,2);

            //Supress warning message if $middle[1] does not exist, Thanks to @crayons
            if ( !isset($middle[1]) ) { $middle[1] = null; }

            $headers[trim($middle[0])] = trim($middle[1]);
        }

        // Print all headers as array
        return $headers;
    }
}