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

    private function __construct()
    {
        $plugin_tree = dir('../plugin');

        while (($file = $plugin_tree->read()) !== false) {
            if (is_file("$file/config.php")) {
                $plugin_config = [];

                require_once("$file/config.php");

                foreach ($plugin_config as $hook_name => $hook_file) {
                    if (!is_array($this->plugin_list[$hook_name]))
                        $this->plugin_list[$hook_name] = [];

                    array_push($this->plugin_list[$hook_name], "$file/$hook_file");
                }
            }
        }

        $plugin_tree->close();
    }

    public function load_hook($hook_name)
    {
        for ($i = 0; $i < count($this->plugin_list[$hook_name]); $i++) {
            require($this->plugin_list[$hook_name][$i]);
        }
    }
}