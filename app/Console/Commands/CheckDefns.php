<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Config;
use App\Models\Definitions\Block as Definition;


class CheckDefns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'astro:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Performs some validation checks on block and layout definitions.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->checkBlocks();
    }

    public function checkBlocks()
    {
        $results = [];
        $path = sprintf('%s/%s/', Config::get('app.definitions_path'), Definition::$defDir);
        $blocks = glob($path . '*/v*', GLOB_ONLYDIR);
        $missing_prefix = "\n *** MISSING: ";
        $err_prefix = "\n *** ERROR: ";
        foreach($blocks as &$block){
            $vue_template = file_exists($block . '/template.vue');
            $twig_template = file_exists($block.'/template.twig');
            $php_class = file_exists($block.'/Block.php');
            $defn = file_exists($block.'/definition.json');
            $name = str_replace($path, '', $block);
            $info = '';
            echo $name;
            $err = '';
            if( $vue_template && $twig_template && $defn ) {
                $info .= '...OK';
            }else{
                $err .= !$vue_template ? $missing_prefix . "Vue Template\n" : "";
                $err .= !$twig_template ? $missing_prefix . "Twig Template\n" : "";
                $err .= !$defn ? $missing_prefix . "JSON Defintion\n" : "";
            }
            if($defn) {
                $json = json_decode(file_get_contents($block.'/definition.json'),true);
                if( !$json ){
                    $err .= $err_prefix . "JSON Definition definition.json empty, invalid or unreadable.\n";
                }else{
                    list($basename,$version) = explode('/v', $name);
                    $errors = $this->checkBlockJSON($json, $basename, $version);
                    if($errors){
                        $err .= " *** definition.json ERRORS:\n\t" . join("\n\t", $errors);
                    }
                }
            }
            $this->info($info);
            $results[$name] = true;
            if( $err ){
                $results[$name] = false;
                $this->error($err);
                echo "\n\n";
            }
        }
    }

    public function checkBlockJSON($json, $name, $version)
    {
        $errors = [];
        foreach( ['label', 'name', 'version'] as $required_attr ){
            if( !isset($json[$required_attr])){
                $errors[] = "Required field $required_attr is missing.";
            }
        }
        if(!empty($json['name']) && $json['name'] != $name){
            $errors[] = "Value for \"name\": (\"{$json['name']}\") does not match definition directory name: \"$name\"";
        }
        if(!empty($json['version']) && $json['version'] != $version){
            $errors[] = "Value for \"version\": (\"{$json['version']}\") does not match definition directory version: \"$version\"";
        }
        return $errors;
    }
}
