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
        //$audio_url = 'http://wordpress4all.com/wp-content/uploads/2024/03/Recording-22.mp3';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.runpod.ai/v2/faster-whisper/runsync',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
            "input": {
                "audio": "' . $audio_url . '",
                "model": "base",
                "transcription": "plain_text",
                "translate": false,
                "language": "en",
                "temperature": 0,
                "best_of": 5,
                "beam_size": 5,
                "patience": 1,
                "suppress_tokens": "-1",
                "condition_on_previous_text": false,
                "temperature_increment_on_fallback": 0.2,
                "compression_ratio_threshold": 2.4,
                "logprob_threshold": -1,
                "no_speech_threshold": 0.6,
                "word_timestamps": false,
                "initial_prompt": "I\'m, uh, is a, you know, Vietnamese ESL student. So, like, I may make some mistake in my grammar."
            },
            "enable_vad": false
        }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bearer " . self::$token,
            ),
        )
        );

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            curl_close($curl);
            return 'CURL Error: ' . $error_msg;
        }

        curl_close($curl);
        $response = json_decode($response, true);
        if ($response['status'] == 'COMPLETED') {
            return $response['output']['transcription'];
        } else {
            $response['error'];
        }
    }
}