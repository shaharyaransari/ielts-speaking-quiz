<?php
namespace ISQNS\External;

class Whisper
{
    public static $token = null;

    public function register()
    {
        self::$token = get_option('isq_whisper_token', null);
    }

    public static function get_transcript($audio_url)
    {
        if (!self::$token) {
            return 'Auth Token Not Given';
        }

        // Download the file from the URL
        $temp_audio_file = tempnam(sys_get_temp_dir(), 'audio') . '.webm';
        $file_content = file_get_contents($audio_url);
        if ($file_content === false) {
            return 'Failed to download audio file';
        }
        file_put_contents($temp_audio_file, $file_content);

        $curl = curl_init();
        $cfile = new \CURLFile($temp_audio_file, 'audio/webm', 'audio.webm');

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.openai.com/v1/audio/transcriptions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'file' => $cfile,
                'model' => 'whisper-1',
                'language' => 'en',
                'prompt' => 'I\'m, uh, is a, you know, Vietnamese ESL student. So, like, I may make some mistake in my grammar.'
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . self::$token,
                'Content-Type: multipart/form-data'
            ),
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            curl_close($curl);
            unlink($temp_audio_file); // Clean up temporary file
            return 'CURL Error: ' . $error_msg;
        }

        curl_close($curl);
        unlink($temp_audio_file); // Clean up temporary file

        $response = json_decode($response, true);
        if (isset($response['text'])) {
            return $response['text'];
        } else {
            return isset($response['error']) ? 'API Error: ' . $response['error']['message'] : 'Unknown Error';
        }
    }
}