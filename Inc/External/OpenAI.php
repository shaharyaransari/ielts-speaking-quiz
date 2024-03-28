<?php
namespace ISQNS\External;

class OpenAI {

    public static $token = null;

    public function register(){
        self::$token = get_option('isq_openai_token', null);
    }

    private static function sendRequest($prompt) {
        if(! self::$token){
            return 'OPEN AI API KEY Not Given';
        }

        $fields = [
            "messages" => [
                ["role" => "system", "content" => "You are a helpful assistant."],
                ["role" => "user", "content" => $prompt]
            ],
            "model" => "gpt-3.5-turbo"
        ];
        $fields = json_encode($fields);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization: Bearer " . self::$token,
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $json_response = json_decode($response, true);
        if($json_response){
            return $json_response['choices'][0]['message']['content'];;
        }else{
            return $response;
        }
    }

    public static function get_improved_answer($transcript) {
        $prompt = <<< EOF
        Here's a transcript of an IELTS Speaking question, provide an improved version while making minimal changes, focus on using natural language:

        $transcript
        
        Improved answer:
        EOF;
        
        return self::sendRequest($prompt);
    }

    public static function get_grammer_suggestions($transcript) {
        $content = $transcript;
        $prompt = <<< EOF
        As a vocabulary expert and linguistics specialist, your role involves a detailed review of the following essay. Your task is to identify any unnatural or incorrect use of vocabulary, especially those not aligning with academic style and formal language, and to suggest natural yet academically appropriate alternatives. Please adhere to these guidelines:

            1. Identify Unnatural or Inappropriate Usage: Look for words or phrases that are either too informal, overly simplistic, or not in line with the principles of academic style/formal language (e.g., emotional language, contractions, idioms).
            2. Suggest Suitable Alternatives: Propose more fitting vocabulary that is both natural and academically appropriate. Avoid overly complex or obscure terms that might detract from the clarity of the text.
            3. Provide Clear Explanations: Offer concise explanations for each change, highlighting how your suggestions improve academic tone while maintaining natural language flow.
            4. Adhere to the Response Format: Your response should follow the given format, including paragraph and line breaks.
            
            Errors and Improvements:
            1. "[original word or phrase]" -&gt; "[improved word or phrase]"
            Explanation: [Explanation].
            
            For example:
            Errors and Improvements:
            1. "Often people think" -&gt; "Frequently, individuals believe"
            Explanation: Replacing "Often people think" with "Frequently, individuals believe" enhances the formal tone of the sentence by using more sophisticated vocabulary.
            2. "students must take all subjects at school" -&gt; "students are required to study all academic disciplines in school"
            Explanation: Replacing "students must take all subjects at school" with "students are required to study all academic disciplines in school" maintains clarity while employing a more precise and elaborate description.
            3. "Everybody thinks that" -&gt; "Most people believe that"
            Explanation: Replacing "Everybody thinks that" with "Most people believe that" avoids overgeneralization, making the statement more precise and academically appropriate.
            
            Now, let's proceed. Please review the following essay and provide your recommendations and explanations:
            
            "$content"
            
            List the errors that you noticed here, suggest better and more advanced alternatives, and provide brief explanations for each recommendation. Do NOT point out entire sentences unless neccessary.
            
            Errors and Improvements:
        EOF;
        return self::sendRequest($prompt);
    }

    public static function get_grammer_score($transcript, $error = 'No Errors') {
        $prompt = <<< EOF
            Based on the transcript and error, choose the option that best describe the grammar level of the speech

            Transcript:
            $transcript
            
            Error:
            $error
            
            Band 9: Structures are precise and accurate at all times.
            Band 8: Wide range of structures, mostly error-free. Occasional errors.
            Band 7: Range of structures used flexibly. Frequent error-free sentences.
            Band 6: Mix of short and complex sentence forms. Errors in complex structures rarely impede communication.
            Band 5: Basic sentence forms controlled. Complex structures attempted but contain errors.
            Band 4: Basic sentence forms attempted. Frequent errors.
            Band 3: Basic sentence forms attempted but with numerous errors.
            Band 2: No evidence of sentence forms. Uses isolated words or memorised utterances.
            Band 1: No rateable language. Mainly uses isolated words.
            
            Answer (Band Score only): 
            EOF;
        return self::sendRequest($prompt);
    }
    public static function get_vocabulary_score($transcript) {
        $prompt = <<< EOF
        Based on the transcript, choose the option that best describe the vocabulary level of the speech

        Transcript:
        $transcript
        
        Options:
        Band 9: Total flexibility with precise use. Sustained accurate and idiomatic language.
        Band 8: Wide resource, flexibly used. Skilful with less common items, despite occasional inaccuracies.
        Band 7: Flexible use of vocabulary. Some less common and idiomatic items used, with occasional inaccuracies.
        Band 6: Sufficient vocabulary for length discussion. Some inappropriate use but generally clear.
        Band 5: Limited flexibility. Attempts at paraphrase not always successful.
        Band 4: Basic vocabulary for familiar topics. Frequent errors with unfamiliar topics.
        Band 3: Very limited vocabulary. Frequent inability to convey basic message.
        Band 2: Extremely limited vocabulary. Speech has virtually no communicative significance.
        Band 1: No communicative vocabulary. Use of isolated words only.
        
        Answer (Band Score only): 
        EOF;
        return self::sendRequest($prompt);
    }
}