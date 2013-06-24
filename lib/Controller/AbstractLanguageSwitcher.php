<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vadym
 * Date: 6/24/13
 * Time: 10:44 PM
 * To change this template use File | Settings | File Templates.
 */
namespace x_ls;
require_once __DIR__."/../../spyc/spyc.php";

abstract class Controller_AbstractLanguageSwitcher extends \AbstractController {
    public $file_extension         = 'yml';
    public $languages              = array();
    public $default_language       = false;
    public $translation_dir_path   = false;
    public $switcher_tag           = 'x_ls_panel';
    public $view_class             = 'x_ls/View_LanguageSwitcher';
    public $var_name               = 'user_panel_lang';
    public $to_same_page           = true;

    function init() {
        parent::init();

		// add add-on locations to pathfinder
		$this->loc = $this->api->locate('addons',__NAMESPACE__,'location');
		$addon_location = $this->api->locate('addons',__NAMESPACE__);
		$this->api->pathfinder->addLocation($addon_location,array(
            'php'=>'lib',
            'template'=>'templates',
            'css'=>'templates/css',
		))->setParent($this->loc);

        $this->api->x_ls = $this;
        if (!$this->translation_dir_path) $this->translation_dir_path = $this->api->pm->base_directory.'translations';
    }

    ////////  translation  ////////
    protected $translations = array();
    function __($string){
        if (count($this->translations)==0) {
            $this->translate();
        }

        if (array_key_exists($string,$this->translations)) {
            return $this->translations[$string];
        } else {
            return (($this->api->getConfig('x_ls/debug',false))?'☺':'').$string;
        }
    }
    public function translate() {
        if($this->model) {
            $t_trans = $this->model->getRows();
            foreach ($t_trans as $t) {
                if (!array_key_exists($this->l,$t)) continue;
                $this->translations[$t['value']] = $t[$this->l];
            }
        } else {
            $files = scandir($this->translation_dir_path);
            foreach ($files as $file) {
                if ($file != $this->getLanguage().'.'.$this->file_extension) continue;
                $this->translations = \Spyc::YAMLLoad($this->translation_dir_path.'/'.$file);
            }
        }
        return $this;
    }
    public function getDefaultLanguage() {
        if ($this->default_language) {
            return $this->default_language;
        } else {
            return $this->languages[0];
        }
    }
    public function addLangSwitcher() {
        $this->api->add($this->view_class,
            array('controller'=>$this),
        'lang_switcher');
    }

}