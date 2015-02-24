<?php

/**
 * Created by PhpStorm.
 * User: Don
 * Date: 2/24/2015
 * Time: 9:29 AM
 */
class plugin

{
    private $plugin_list = [];

    function __construct()
    {
        $plugin_tree = scandir('../plugins/');

        for ($h = 2; $h < count($plugin_tree); $h++) {
            $file = "../plugins/$plugin_tree[$h]";
            if (is_file("$file/config.php")) {
                $plugin_config = [];

                require_once("$file/config.php");

                foreach ($plugin_config as $hook_name => $hook_file) {
                    if (!isset($this->plugin_list[$hook_name]))
                        $this->plugin_list[$hook_name] = [];

                    array_push($this->plugin_list[$hook_name], "$file/$hook_file");
                }
            }
        }
    }

    public function load_hook($hook_name)
    {
        for ($i = 0; $i < count($this->plugin_list[$hook_name]); $i++) {
            require($this->plugin_list[$hook_name][$i]);
        }
    }
}