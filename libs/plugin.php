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

        $plugin_tree_serialize = serialize($plugin_tree);
        $cache_file_name = '../dbs/plugin_cache';
        $cache_content = NULL;

        if (is_file($cache_file_name)) {
            $cache_content = file_get_contents($cache_file_name);

            if (trim($plugin_tree_serialize) != trim($cache_content)) {
                $this->build_cache($plugin_tree, $cache_file_name);
            }
        } else {
            $this->build_cache($plugin_tree, $cache_file_name);
        }

        $this->config['public_page'] = [];

        for ($h = 2; $h < count($plugin_tree); $h++) {
            $file = "../plugins/$plugin_tree[$h]";

            if (is_dir($file) && is_file("$file/config.php")) {
                $plugin_config = [];
                $plugin_info = [];
                $plugin_injector = [];
                $plugin_public_page = [];

                require("$file/config.php");

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

    private function build_cache($plugin_tree, $cache_location)
    {
        $cache_file_list = ['custom.css', 'custom.js'];
        $cache_list_content = serialize($plugin_tree);
        $cache_file_content = [];
        $cache_file_dir = '../dbs/cache';

        file_put_contents($cache_location, $cache_list_content, LOCK_EX);

        for ($h = 2; $h < count($plugin_tree); $h++) {
            if (!is_dir("../plugins/$plugin_tree[$h]"))
                continue;

            $dir = "../plugins/$plugin_tree[$h]/custom";

            if (!is_dir($dir))
                continue;

            for ($g = 0; $g < count($cache_file_list); $g++) {
                $custom_file_name = "$dir/$cache_file_list[$g]";
                if (is_file($custom_file_name)) {
                    $custom_file_content = file_get_contents($custom_file_name);
                    $cache_file_content[$g][] = "$custom_file_content\n\n";
                }
            }
        }

        for ($g = 0; $g < count($cache_file_list); $g++) {
            $cache_file = implode($cache_file_content[$g]);
            file_put_contents("$cache_file_dir/$cache_file_list[$g]", $cache_file, LOCK_EX);
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