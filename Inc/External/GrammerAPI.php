<?php
namespace ISQNS\External;
class GrammerAPI{
    public static $username = null;
    public static $apiKey = null;

    public function register(){
        self::$username = get_option('isq_lt_username', null);
        self::$apiKey = get_option('isq_lt_api_key', null);
    }
    public static function get_corrections($text){
            if(! (self::$username && self::$apiKey)){
                return null;
            }
            $text = stripslashes($text);
            $curl = curl_init();
            $username = self::$username;
            $apiKey = self::$apiKey;
            // $text = 'I seen her at the store yesterday. Me and her talked about the upcoming party. She don\'t know what to wear yet, but I told her to not worry.';
            $language =  'en-US';
            $enabledOnly = 'false';
            $level = 'picky';
            $defaults = array(
                'language' => $language,
                'username' => $username,
                'apiKey' => $apiKey,
                'enabledOnly' => $enabledOnly,
                'disabledCategories' => 'PUNCTUATION',
                'level' => $level
            );
            $user_data = array(
                'text' => $text,
            );
            $data = array_merge($user_data, $defaults);
            $encodedData = http_build_query($data, "", null, PHP_QUERY_RFC3986);
            // return $encodedData;

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.languagetoolplus.com/v2/check',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $encodedData,
            CURLOPT_HTTPHEADER => array(
                'accept: application/json'
            ),
            ));

            $response = curl_exec($curl);
            $json_response = json_decode($response);
            curl_close($curl);
            if($json_response){
                return $json_response;
            }else{
                return $response;
            }
    }
}