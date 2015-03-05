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

    public $config = [];

    function __construct()
    {
        $plugin_tree = scandir('../plugins/');
        $this->config['public_page'] = [];

        for ($h = 2; $h < count($plugin_tree); $h++) {
            $file = "../plugins/$plugin_tree[$h]";

            if (is_dir($file) && is_file("$file/config.php")) {
                $plugin_config = [];
                $plugin_info = [];
                $plugin_injector = [];
                $plugin_public_page = [];

                require_once("$file/config.php");

                foreach ($plugin_injector as $hook_name => $hook_file) {
                    if (!isset($this->plugin_list[$hook_name]))
                        $this->plugin_list[$hook_name] = [];

                    array_push($this->plugin_list[$hook_name], "$file/$hook_file");
                }

                foreach ($plugin_public_page as $page_name => $file_location) {
                    $this->config['public_page'][$page_name] = [
                        'page_location' => "$file/$file_location",
                        'page_dir' => $file
                    ];
                }

                $this->config[$plugin_info['IDENTIFICATION']] = $plugin_config;
                $this->config[$plugin_info['IDENTIFICATION']]['dir_location'] = $file;
            }
        }
    }

    public function load_hook($hook_name)
    {
        if (isset($this->plugin_list[$hook_name])) {
            for ($i = 0; $i < count($this->plugin_list[$hook_name]); $i++) {
                require($this->plugin_list[$hook_name][$i]);
            }
        }
    }

    public function load_public_page($page_name)
    {
        if (!isset($this->config['public_page'][$page_name]))
            response_message(403, "Invalid request.");

        if (!is_file($this->config['public_page'][$page_name]['page_location']))
            response_message(404, "Can't get the template.");

        $return = [
            'location' => $this->config['public_page'][$page_name]['page_dir'],
            'content' => file_get_contents($this->config['public_page'][$page_name]['page_location'])
        ];

        echo(json_encode($return));
        exit();
    }

    public function load_api($plugin_identification)
    {
        $api_file_location = $this->config[$plugin_identification]['dir_location'] . '/api.php';

        if (!is_file($api_file_location))
            response_message(403, 'Api file does not exists');

        require($api_file_location);
        exit();
    }
}