<?php
namespace ISQNS\External;
class PronunciationAPI{
    public static $speech_key = null;

    public function register(){
        self::$speech_key = get_option('isq_pronun_key', null);
    }
    public static function get_pronunciation_data($audioUrl,$transcript){
        $speech_key = self::$speech_key;
        if(!$speech_key){
            return null;
        }
        $curl = curl_init();
        $defaults = array(
            'grading_system' => 'HundredMark',
            'grantularity' => 'Phoneme',
            'dimension' => 'Comprehensive',
            'enable_prosody' => 'true'
        );
        $user_data = array(
            'url' => $audioUrl,
            'reference_text' => $transcript,
        );
        $data = array_merge($user_data, $defaults);
        $encodedData = http_build_query($data);
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'http://api2.ieltsscience.fun:8080/',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>  $encodedData,
          CURLOPT_HTTPHEADER => array(
            'accept: application/json',
            "speech-key: $speech_key",
            'service-region: eastus'
          ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);
        $validated_response = self::validateJson($response);
        $json_response = json_decode($validated_response);
        if($json_response){
            return $json_response;
        }else{
            return $response;
        }
    }

    public static function validateJson($response){
        $res_array = explode('data: ',$response);
        array_pop($res_array);
        if($res_array[0] == ''){
            array_shift($res_array);
        }
        $res_json = implode(',', $res_array);
        $res_json = "[$res_json]";
        $res_json = stripslashes($res_json);
        return $res_json;
    }
}