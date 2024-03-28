<?php
namespace ISQNS;

class Init{
    public static function get_classes(){
        return [
            Admin\SpeakingQuizCPT::class,
            Admin\SpeakingPartsCPT::class,
            Admin\SpeakingQuizQuestionsCPT::class,
            Admin\Admin::class,
            Admin\Helpers::class,
            Base\AjaxHooks::class,
            Base\ScriptsManager::class,
            Base\Shortcodes::class,
            Base\TemplateManager::class,
            Quiz\AjaxHooks::class,
            Result\AjaxHooks::class,
            QuizBuilder\AjaxHooks::class,
            External\OpenAI::class,
            External\GrammerAPI::class,
            External\Whisper::class,
            External\PronunciationAPI::class,
            LearnDash\Quiz::class
        ];
    }

    public static function register_classes(){
        foreach (self::get_classes() as $class) {
            $service = self::instantiate($class);
            if(method_exists($service, 'register')){
                $service->register();
            }
        }
    }

    private static function instantiate($class){
        return new $class();
    }
}