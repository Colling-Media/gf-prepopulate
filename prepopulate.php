<?php
/*
Plugin Name: GF Pre-Populate
Plugin URI: https://github.com/Colling-Media/GF-PrePopulate
Description: A simple add-on to pre-populate fields based on query parameters or cookies.
Version: 1.0.0
Author: Colling Media
Author URI: https://github.com/Colling-Media/
*/

if (class_exists("GFForms")) {
    GFForms::include_addon_framework();

    class GFPrePopulate extends GFAddOn {

        protected $_version = "1.0.0";
        protected $_min_gravityforms_version = "1.7.9999";
        protected $_slug = "prepopulate";
        protected $_path = "prepopulate/prepopulate.php";
        protected $_full_path = __FILE__;
        protected $_title = "GF Pre-Populate";
        protected $_short_title = "GF Pre-Populate";

        /**
         * Pre Init
         *
         * Initializes the addon.
         *
         * @since 1.0.0
         *
         * @return void
         */
        public function pre_init() {
            parent::pre_init();
            $this->create_actions();
        }

        /**
         * Create Actions
         *
         * Creates the actions to set up
         * the form fields you are pre
         * populating.
         *
         * @since 1.0.0
         *
         * @return void
         */
        private function create_actions()
        {
            $fields = $this->getSetting('parameters');
            $cookieExpiration = $this->calculateExpiration();

            foreach ($fields AS $field) {
                add_filter('gform_field_value_' . $field, [$this, 'handle_action'], 10, 3 );
                if (isset($_REQUEST[$field])) {
                    setcookie($field, $_REQUEST[$field], $cookieExpiration, "/");
                }
            }
        }

        /**
         * Handle Action
         *
         * Handles the action from
         * Wordpress for setting up
         * the form field.
         *
         * @since 1.0.0
         *
         * @param $value
         * @param $field
         * @param $name
         *
         * @return mixed
         */
        public function handle_action($value, $field, $name) {
            if ($field->allowsPrepopulate) {
                if (isset($_COOKIE[$name])) {
                    return $_COOKIE[$name];
                }
                if (isset($_REQUEST[$name])) {
                    return $_REQUEST[$name];
                }
            }
        }

        /**
         * Plugin Settings Fields
         *
         * Create the settings page for the Pre-Populate plugin
         *
         * @since 1.0.0
         *
         * @return array
         */
        public function plugin_settings_fields()
        {
            return array(
                array(
                    "title"  => "Pre-Populate Settings",
                    "fields" => array(
                        array(
                            "name"    => "parameters",
                            "tooltip" => "Example: utm_source,utm_campaign,utm_medium",
                            "label"   => "Query Parameters",
                            "type"    => "text",
                            "class"   => "large",
                        ),
                        array(
                            "name"    => "cookieExpiration",
                            "tooltip" => "Cookie Expiration in days. Set to 0 for session duration. You can use decimals for parts of a day (0.5 = 12 hours).",
                            "label"   => "Cookie Expiration",
                            "type"    => "text",
                            "class"   => "large",
                        )
                    )
                ),
            );
        }

        /**
         * Calculate Expiration
         *
         * Calculate the expiration
         * time for any cookies set
         * based on the expiration
         * time settings.
         *
         * @since 1.0.0
         *
         * @return array|int|mixed
         */
        private function calculateExpiration() {
            $time = $this->getSetting('cookieExpiration');
            if ($time == 0 || $time == '') {
                return 0;
            } else {
                return time()+60*60*24*$time;
            }
        }

        /**
         * Get Setting
         *
         * Returns the settings for the
         * plugin, either parsed or just
         * the string.
         *
         * @since 1.0.0
         *
         * @param $item
         *
         * @return array|mixed
         */
        private function getSetting($item)
        {
            $val = $this->get_plugin_setting($item);
            if (strpos($val, ',') !== false)
            {
                return explode(',', $val);
            } else {
                return $val;
            }
        }
    }

    new GFPrePopulate();
}
