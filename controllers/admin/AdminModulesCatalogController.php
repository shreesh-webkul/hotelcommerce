<?php
/**
* 2010-2020 Webkul.
*
* NOTICE OF LICENSE
*
* All right is reserved,
* Please go through this link for complete license : https://store.webkul.com/license.html
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade this module to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to https://store.webkul.com/customisation-guidelines/ for more information.
*
*  @author    Webkul IN <support@webkul.com>
*  @copyright 2010-2020 Webkul IN
*  @license   https://store.webkul.com/license.html
*/

class AdminModulesCatalogControllerCore extends AdminController
{

    public $modules;

    const SUGGESTION_CONTENT = '/cache/suggestion.html';

    const ELEMENT_TYPE_MODULE = 1;
    const ELEMENT_TYPE_THEME = 2;

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initContent()
    {
        parent::initContent();
        $this->warmUp();

        $modules = Module::getSuggestedModules();
        array_map(function($value) {
            $this->fillModuleData($value, 'array');
            $value->element_type = self::ELEMENT_TYPE_MODULE;
            return $value;
        }, $modules);
        $this->sortList($modules, 'module');

        $this->modules = $modules;
        $this->themes = $this->getSuggestedThemes();
        $this->assignSortCriteria();
        $this->context->smarty->assign(array(
            'modules' => $this->modules,
            'themes' => $this->themes,
            'modules_uri' => __PS_BASE_URI__.basename(_PS_MODULE_DIR_),
            'element_type_module' => self::ELEMENT_TYPE_MODULE,
            'element_type_theme' => self::ELEMENT_TYPE_THEME,

        ));
    }

    public function assignSortCriteria()
    {
        $sortCriterta = array(
            array (
                'key' => 'relevence',
                'value' => 'relevence',
                'title' => $this->l('Relevance')
            ),
            array (
                'key' => 'name',
                'value' => 'name',
                'title' => $this->l('Name')
            ),
            array (
                'key' => 'price_increasing',
                'value' => 'price_increasing',
                'title' => $this->l('Price (low to high)')
            ),
            array (
                'key' => 'price_decreasing',
                'value' => 'price_decreasing',
                'title' => $this->l('Price (high to low)')
            ),
        );
        $this->context->smarty->assign(array(
            'sort_criterta' => $sortCriterta,
            'module_sort' => Configuration::get('PS_SORT_MODULE_MODULES_CATALOG_'.(int)$this->context->employee->id),
            'theme_sort' => Configuration::get('PS_SORT_THEME_MODULES_CATALOG_'.(int)$this->context->employee->id),
        ));
    }

    public function getSuggestedThemes()
    {
        $installedThemes = array();
        $themes = Theme::getAllThemes()->getAll();
        foreach ($themes as $theme) {
            $installedThemes[] = Theme::getThemeInfo($theme->id);
        }
        $files_list = array(
            array('type' => 'addonsMustHave', 'file' => _PS_ROOT_DIR_.Module::CACHE_FILE_MUST_HAVE_MODULES_LIST, 'loggedOnAddons' => 0),
        );

        $theme_list = array();
        foreach ($files_list as $f) {
            $file = $f['file'];
            $content = Tools::file_get_contents($file);
            $xml = @simplexml_load_string($content, null, LIBXML_NOCDATA);
            if ($xml && isset($xml->theme)) {
                foreach ($xml->theme as $modthemes) {
                    foreach ($installedThemes as $theme) {
                        if ($theme['theme_name'] == $modthemes->name)
                            continue;
                    $item = new stdClass();
                    $item->id = 0;
                    $item->warning = '';
                    $item->type = strip_tags((string)$f['type']);
                    $item->element_type = self::ELEMENT_TYPE_THEME;
                    $item->name = strip_tags((string)$modthemes->name);
                    $item->version = strip_tags((string)$modthemes->version);
                    $item->displayName = strip_tags((string)$modthemes->displayName);
                    $item->description = stripslashes(strip_tags((string)$modthemes->description));
                    $item->description_full = stripslashes(strip_tags((string)$modthemes->description_full));
                    $item->author = strip_tags((string)$modthemes->author);
                    $item->limited_countries = array();
                    $item->parent_class = '';
                    $item->onclick_option = false;
                    $item->available_on_addons = 1;
                    $item->active = 0;
                    $item->additional_description = isset($modthemes->additional_description) ? stripslashes($modthemes->additional_description) : null;
                    $item->compatibility = isset($modthemes->compatibility) ? (array)$modthemes->compatibility : null;
                    $item->nb_rates = isset($modthemes->nb_rates) ? (array)$modthemes->nb_rates : null;
                    $item->avg_rate = isset($modthemes->avg_rate) ? (array)$modthemes->avg_rate : null;
                    $item->badges = isset($modthemes->badges) ? (array)$modthemes->badges : null;
                    $item->url = isset($modthemes->url) ? $modthemes->url : null;

                    if (isset($modthemes->img)) {
                        if (!file_exists(_PS_TMP_IMG_DIR_.md5((int)$modthemes->id.'-'.$modthemes->name).'.jpg')) {
                            if (!file_put_contents(_PS_TMP_IMG_DIR_.md5((int)$modthemes->id.'-'.$modthemes->name).'.jpg', Tools::file_get_contents($modthemes->img))) {
                                copy(_PS_IMG_DIR_.'404.gif', _PS_TMP_IMG_DIR_.md5((int)$modthemes->id.'-'.$modthemes->name).'.jpg');
                            }
                        }

                        if (file_exists(_PS_TMP_IMG_DIR_.md5((int)$modthemes->id.'-'.$modthemes->name).'.jpg')) {
                            $item->image = '../img/tmp/'.md5((int)$modthemes->id.'-'.$modthemes->name).'.jpg';
                        }
                    }

                    if ($item->type == 'addonsMustHave') {
                        $item->addons_buy_url = strip_tags((string)$modthemes->url);
                        $prices = (array)$modthemes->price;
                        $id_default_currency = Configuration::get('PS_CURRENCY_DEFAULT');

                        foreach ($prices as $currency => $price) {
                            if ($id_currency = Currency::getIdByIsoCode($currency)) {
                                $item->price = (float)$price;
                                $item->id_currency = (int)$id_currency;

                                if ($id_default_currency == $id_currency) {
                                    break;
                                }
                            }
                        }
                    }
                    $theme_list[$modthemes->id.'-'.$item->name] = $item;
                    }
                }
            }
        }
        $theme_list = array_values($theme_list);
        $this->sortList($theme_list, 'theme');

        return $theme_list;
    }

    public function getSuggestionContent()
    {
        return Tools::addonsRequest('suggestion');
        if (!$this->isFresh(self::SUGGESTION_CONTENT, 86400)) {
            @file_put_contents(_PS_ROOT_DIR_.self::SUGGESTION_CONTENT, Tools::addonsRequest('suggestion'));
        }
        if (file_exists(_PS_ROOT_DIR_.self::SUGGESTION_CONTENT)) {
            return Tools::file_get_contents(_PS_ROOT_DIR_.self::SUGGESTION_CONTENT);
        }
        return false;
    }

    public function getSuggestedModules()
    {
        $files_list = array(
            array('type' => 'addonsMustHave', 'file' => _PS_ROOT_DIR_.Module::CACHE_FILE_MUST_HAVE_MODULES_LIST, 'loggedOnAddons' => 0),
            array('type' => 'addonsNative', 'file' => _PS_ROOT_DIR_.Module::CACHE_FILE_DEFAULT_COUNTRY_MODULES_LIST, 'loggedOnAddons' => 0),
        );
        $module_list = array();
        foreach ($files_list as $f) {
            $file = $f['file'];
            $content = Tools::file_get_contents($file);
            $xml = @simplexml_load_string($content, null, LIBXML_NOCDATA);
            if ($xml && isset($xml->module)) {
                foreach ($xml->module as $modaddons) {
                    $item = new stdClass();
                    $item->id = 0;
                    $item->warning = '';
                    $item->type = strip_tags((string)$f['type']);
                    $item->element_type = self::ELEMENT_TYPE_MODULE;
                    $item->name = strip_tags((string)$modaddons->name);
                    $item->version = strip_tags((string)$modaddons->version);
                    $item->tab = strip_tags((string)$modaddons->tab);
                    $item->displayName = strip_tags((string)$modaddons->displayName);
                    $item->description = stripslashes(strip_tags((string)$modaddons->description));
                    $item->description_full = stripslashes(strip_tags((string)$modaddons->description_full));
                    $item->author = strip_tags((string)$modaddons->author);
                    $item->limited_countries = array();
                    $item->parent_class = '';
                    $item->onclick_option = false;
                    $item->is_configurable = 0;
                    $item->need_instance = 0;
                    $item->not_on_disk = 1;
                    $item->available_on_addons = 1;
                    $item->trusted = Module::isModuleTrusted($item->name);
                    $item->active = 0;
                    $item->description_full = stripslashes($modaddons->description_full);
                    $item->additional_description = isset($modaddons->additional_description) ? stripslashes($modaddons->additional_description) : null;
                    $item->compatibility = isset($modaddons->compatibility) ? (array)$modaddons->compatibility : null;
                    $item->nb_rates = isset($modaddons->nb_rates) ? (array)$modaddons->nb_rates : null;
                    $item->avg_rate = isset($modaddons->avg_rate) ? (array)$modaddons->avg_rate : null;
                    $item->badges = isset($modaddons->badges) ? (array)$modaddons->badges : null;
                    $item->url = isset($modaddons->url) ? $modaddons->url : null;
                    $item->is_native = false;

                    if (isset($modaddons->img)) {
                        if (!file_exists(_PS_TMP_IMG_DIR_.md5((int)$modaddons->id.'-'.$modaddons->name).'.jpg')) {
                            if (!file_put_contents(_PS_TMP_IMG_DIR_.md5((int)$modaddons->id.'-'.$modaddons->name).'.jpg', Tools::file_get_contents($modaddons->img))) {
                                copy(_PS_IMG_DIR_.'404.gif', _PS_TMP_IMG_DIR_.md5((int)$modaddons->id.'-'.$modaddons->name).'.jpg');
                            }
                        }

                        if (file_exists(_PS_TMP_IMG_DIR_.md5((int)$modaddons->id.'-'.$modaddons->name).'.jpg')) {
                            $item->image = '../img/tmp/'.md5((int)$modaddons->id.'-'.$modaddons->name).'.jpg';
                        }
                    }

                    if ($item->type == 'addonsMustHave') {
                        $item->addons_buy_url = strip_tags((string)$modaddons->url);
                        $prices = (array)$modaddons->price;
                        $id_default_currency = Configuration::get('PS_CURRENCY_DEFAULT');

                        foreach ($prices as $currency => $price) {
                            if ($id_currency = Currency::getIdByIsoCode($currency)) {
                                $item->price = (float)$price;
                                $item->id_currency = (int)$id_currency;

                                if ($id_default_currency == $id_currency) {
                                    break;
                                }
                            }
                        }
                    }
                    // ppp($item);
                    // $this->fillModuleData($item, 'array');
                    // ddd($item);

                    $module_list[$modaddons->id.'-'.$item->name] = $item;
                    $module_list[$modaddons->id.'-'.$item->name.'1'] = $item;
                    $module_list[$modaddons->id.'-'.$item->name.'2'] = $item;
                    $module_list[$modaddons->id.'-'.$item->name.'3'] = $item;
                    $module_list[$modaddons->id.'-'.$item->name.'3d'] = $item;
                    $module_list[$modaddons->id.'-'.$item->name.'312'] = $item;
                }
            }
        }
        $module_list = array_values($module_list);
        $modules_name = array_column($module_list, 'name');
        $modulesToAdd = array();
        $installedModules = ModuleCore::getModulesOnDisk();
        foreach ($installedModules as $installedMod) {
            if (($id = array_search($installedMod->name, $modules_name)) !== false) {
                if ($installedMod->installed) {
                    unset($module_list[$id]);
                }
            } else {
                if (!$installedMod->installed) {
                    $installedMod->element_type = self::ELEMENT_TYPE_MODULE;
                    // $this->fillModuleData($installedMod, 'array');
                    $modulesToAdd[] = $installedMod;
                }
            }
        }
        $module_list = array_merge($module_list, $modulesToAdd);
        $this->sortList($module_list, 'module');
        return $module_list;
    }

    public function sortList(&$list, $type)
    {

        switch($type) {
            case 'module':
                $criteria = Configuration::get('PS_SORT_MODULE_MODULES_CATALOG_'.(int)$this->context->employee->id);
                break;
            case 'theme':
                $criteria = Configuration::get('PS_SORT_THEME_MODULES_CATALOG_'.(int)$this->context->employee->id);
                break;
        }
        if ($criteria != 'relevence') {
            usort($list, function($a, $b) use($criteria){
                if ($criteria == 'name') {
                    return strnatcasecmp($a->displayName, $b->displayName);
                } else if ($criteria == 'price_increasing') {
                    if (isset($a->price) && isset($b->price))
                        return $a->price > $b->price;
                    else if (isset($b->price) && $b->price)
                        return true;
                } else if ($criteria == 'price_decreasing') {
                    if (isset($a->price) && isset($b->price))
                        return $a->price < $b->price;
                    else if (isset($b->price) && $b->price)
                        return true;
                }

            });
        }
        return true;
    }

    protected function setSorting($module_sorting, $theme_sorting)
    {
        Configuration::updateValue('PS_SORT_MODULE_MODULES_CATALOG_'.(int)$this->context->employee->id, $module_sorting);
        Configuration::updateValue('PS_SORT_THEME_MODULES_CATALOG_'.(int)$this->context->employee->id, $theme_sorting);
    }


    public function ajaxProcessGetSuggestionContent()
    {
        $response = array('success' => false);
        if ($content = $this->getSuggestionContent()) {
            $response['success'] = true;
            $response['content'] = $content;
        }
        $this->ajaxDie(json_encode($response));
    }

    public function ajaxProcessSetSorting()
    {
        $this->setSorting(Tools::getValue('module_sorting'), Tools::getValue('theme_sorting'));
        $this->ajaxDie(json_encode(array(
            'success' => true
        )));
    }


    protected function warmUp()
    {
        if (!$this->isFresh(Module::CACHE_FILE_DEFAULT_COUNTRY_MODULES_LIST, 86400)) {
            file_put_contents(_PS_ROOT_DIR_.Module::CACHE_FILE_DEFAULT_COUNTRY_MODULES_LIST, Tools::addonsRequest('native'));
        }

        if (!$this->isFresh(Module::CACHE_FILE_MUST_HAVE_MODULES_LIST, 86400)) {
            @file_put_contents(_PS_ROOT_DIR_.Module::CACHE_FILE_MUST_HAVE_MODULES_LIST, Tools::addonsRequest('must-have'));
        }
    }

    public function setMedia()
    {
        parent::setMedia();
        $this->addJS(_PS_JS_DIR_.'admin/modules_catalog.js');
    }
}