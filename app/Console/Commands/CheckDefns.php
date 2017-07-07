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
        $all_blocks = $this->checkBlocks();
        $index = $this->checkBlockIndex();
        $this->line("\n****** Exported Blocks (index.js) ******\n");
        if($index['errors']){
            $this->error("*** index.js ***\n\n" . join("\n", $index['errors']));
            $this->line("");
        }

        $unused = [];
        foreach($all_blocks as $block => $status){
            $block = str_replace('/', '-', $block);
            if(!in_array($block, $index['exported'])){
                $unused[] = $block;
            }
        }

        $this->info("The following blocks are exported:\n\n" . join("\n", $index['exported']) . "\n");
        if(count($unused)){
            $this->comment("The following blocks are NOT exported:\n\n\t" . join("\n\t", $unused) . "\n");
        }
        $this->alert("Not checked: - layouts (definition.json and layouts.js file) and regions (definition.json).");
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
            echo $name . '...';
            $err = '';
            if( $vue_template && $twig_template && $defn ) {
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
                        $err .= "\n*** definition.json ***\n\n" . join("\n", $errors) . "\n";
                    }
                }
            }
            if(!$err){
                $info .= 'OK';
                $this->info($info);
            }
            $results[$name] = true;
            if( $err ){
                $this->error('*ERROR*');
                $results[$name] = false;
                $this->error($err);
                echo "\n";
            }else{
            }
        }
        return $results;
    }

    public function checkBlockIndex()
    {
        $ok = [];
        $errors = [];
        $file = Config::get('app.definitions_path') . '/index.js';
        if(!file_exists($file)){
            $errors[] = "Missing index.js in " . Config.get('app.definitions_path');
        }else{
            $index = file_get_contents($file);
            $imports = [];
            if( preg_match_all('/^\s*import\s+([a-z0-9_]+)\s+from\s+([\'"])\.\/blocks\/([a-z0-9_-]+)\/v([0-9]+)\/[a-z0-9_.-]+\2;$/im', $index, $matches)){
                for($i = 0; $i < count($matches[0]); $i++){
                   $imports[$matches[1][$i]] = ['name' => $matches[3][$i], 'version' => $matches[4][$i]];
                }
            }

            $exports = [];
            if( preg_match('/^.*?export\s+default\s*{\s*([^}]+?)\s*}.*?$/im', $index, $matches)){
                $index = $matches[1];
                if( preg_match_all('/\s*([\'"])([a-z0-9_-]+)\1\s*:\s*([a-z0-9_]+)[\s,]?/im', $index, $matches)){
                    for($i = 0; $i < count($matches[1]);$i++){
                        $exports[$matches[2][$i]] = $matches[3][$i];
                    }
                }
            }
            ksort($imports);
            ksort($exports);
            foreach($exports as $k =>$v){
                if(!isset($imports[$v])){
                    $errors[] = "\"$k\" is exported but not imported.";
                }elseif($imports[$v]['name'].'-v' . $imports[$v]['version'] != $k) {
                    $errors[] = "No matching import for export \"$k\": \"$v\"";
                }else{
                    $ok[] = $k;
                }
            }
        }
        return ['errors' => $errors, 'exported' => $ok];
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
        if(!empty($json['fields'])) {
            $err = $this->checkBlockFields($json['fields']);
            if($err){
                $errors[] = "Invalid field definitions:\n\t" . join("\n\t", $err) . "\n";
            }
        }
        return $errors;
    }

    public function checkBlockFields($fields)
    {
        $errors = [];
        $valid_fields = [
            'text' => '',
        	'textarea' => '',
            'richtext' => '',
            'switch' => '',
            'checkbox' => '',
            'select' => '',
            'multiselect' => '',
            'radio' => '',
            'buttongroup' => '',
            'link' => '',
            'image' => '',
            'file' => '',
            'number' => '',
            'slider' => '',
            'date' => '',
            'time' => '',
            'datetime' => '',
            'nested' => '',
            'collection' => '',
            'group' => ''
        ];
        if(!is_array($fields)){
            $errors[] = 'fields must be an array if present.';
        }
        foreach($fields as $field){
            if( !is_array($field)){
                $errors[] = $field . ' is not a valid field definition (array required).';
            }else{
                if(empty($field['name'])){
                    $errors[] = 'Field missing name.';
                }else{
                    if(empty($field['type'])){
                        $errors[] = "Field \"{$field['name']}\": \"type\" is required.";
                    }elseif(!isset($valid_fields[$field['type']])){
                        $errors[] = "Field \"{$field['name']}\": \"{$field['type']}\" is not a valid type.";
                    }elseif(!empty($field['validation'])){ // checking validation requires a valid type
                        $err = $this->checkFieldValidation($field['name'], $field['type'], $field['validation']);
                        if($err){
                            $errors[] = "Field \"{$field['name']}\": Invalid validation rules: \n\t" . join("\n\t", $err);
                        }
                    }
                    if(empty($field['label'])){
                        $errors[] = "Field \"{$field['name']}\": \"label\" is required.";
                    }
                }
            }
        }
        return $errors;
    }

    public function checkFieldValidation($name, $type, $rules)
    {
        return [];
    }
}
