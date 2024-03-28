<?php
namespace ISQNS\Base;

// Admin Helper Functions 
class SettingsAPI {

    public $settings = array();
    public $sections = array();
    public $fields = array();

    public function register(){ // we will call this function only when $settings, $sections, $fields are not empty
        if($this->settings){
            add_action( 'admin_init', array($this, 'add_custom_settings'));
        }
    }
    // Setter For Settings 
    public function set_settings( array $settings ){
        $this->settings = $settings;
        return $this;
    }

    // Setter for Sections 
    public function set_sections( array $sections ){
        $this->sections = $sections;
        return $this;
    }
    // Setter For Fields 
    public function set_fields( array $fields ){
        $this->fields = $fields;
        return $this;
    }


    public function add_custom_settings(){
        // Register Settings
        foreach($this->settings as $setting){
            register_setting( $setting['option_group'], $setting['option_name'], $setting['args'] );
        }
        // Add Sections
        foreach($this->sections as $section){
            add_settings_section( $section['id'], $section['title'], (isset($section['callback']) ? $section['callback'] : ''), $section['page'], $section['args']);
        }
        // Add Fields
        foreach($this->fields as $field){
            add_settings_field( $field['id'], $field['title'], (isset($field['callback']) ? $field['callback'] : ''), $field['page'], $field['section'], $field['args'] );
        }
    }
}