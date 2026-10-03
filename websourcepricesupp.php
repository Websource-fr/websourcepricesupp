<?php
/**
 * 2007-2024 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2024 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class WebsourcePriceSupp extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'websourcepricesupp';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'Websource';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Websource : Bouton d\'arrondi du panier');
        $this->description = $this->l('Arrondi supérieur du panier');

        $this->confirmUninstall = $this->l('Are you sure');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /* ------------------------------------------------------------------
     * Détection de la version de PrestaShop et du thème
     * ---------------------------------------------------------------- */

    /** true en PrestaShop 1.7 et au-delà (Classic, Hummingbird, Warehouse...). */
    public static function is17()
    {
        return version_compare(_PS_VERSION_, '1.7', '>=');
    }

    /** Nom du thème actif + de son parent, en minuscules (vide en 1.6). */
    public function themeNames()
    {
        $shop = $this->context->shop;
        $t = isset($shop->theme) ? $shop->theme : null;
        if (!is_object($t) || !method_exists($t, 'getName')) {
            return '';
        }
        $parent = method_exists($t, 'get') ? (string) $t->get('parent') : '';

        return Tools::strtolower($t->getName() . ' ' . $parent);
    }

    /**
     * Famille de thème : '16', 'hummingbird', 'warehouse', 'classic' ou 'other'
     * (thème inconnu : balisage Bootstrap générique).
     */
    public function themeFamily()
    {
        if (!self::is17()) {
            return '16';
        }
        $n = $this->themeNames();
        foreach (array('hummingbird', 'warehouse', 'classic') as $f) {
            if (strpos($n, $f) !== false) {
                return $f;
            }
        }

        return 'other';
    }

    /** Prix formaté (Tools::displayPrice a disparu en PrestaShop 9). */
    public function formatPrice($amount)
    {
        $currency = $this->context->currency;
        if (method_exists($this->context, 'getCurrentLocale') && $this->context->getCurrentLocale()) {
            return $this->context->getCurrentLocale()->formatPrice((float) $amount, $currency->iso_code);
        }

        return Tools::displayPrice((float) $amount, $currency);
    }

    /**
     * Installation. Les 99 produits « Arrondi » sont créés à la demande par le
     * contrôleur roundcart (plus de création en masse à l'installation).
     */
    public function install()
    {
        try {
            require_once _PS_MODULE_DIR_ . '/' . $this->name . '/sql/install.php';

            if (!parent::install()) {
                return false;
            }

            $hooksOk = $this->registerHook('displayHeader')
                && $this->registerHook('actionValidateOrder')
                && $this->registerHook('displayOverrideTemplate')
                && $this->registerHook('displayShoppingCartFooter')
                && $this->registerHook('displayBackOfficeHeader');

            if (!$hooksOk) {
                $this->uninstall();

                return false;
            }

            Configuration::updateValue('WEBSOURCEPRICESUPP_LIVE_MODE', false);

            return true;
        } catch (Exception $e) {
            return $this->abortInstall();
        } catch (Throwable $e) { // PHP 7+ (TypeError, Error...)
            return $this->abortInstall();
        }
    }

    /** Never leave the module half-installed: clean up so the next attempt starts fresh. */
    private function abortInstall()
    {
        try {
            $this->uninstall();
        } catch (Exception $e) {
            // best effort
        } catch (Throwable $e) {
            // best effort
        }

        return false;
    }

    public function uninstall()
    {
        Configuration::deleteByName('WEBSOURCEPRICESUPP_LIVE_MODE');

        try {
            require_once _PS_MODULE_DIR_ . '/' . $this->name . '/sql/uninstall.php';
        } catch (Exception $e) {
            // best effort
        } catch (Throwable $e) {
            // best effort
        }

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitWebsourcePriceSuppModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->supportBlock() . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitWebsourcePriceSuppModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'WEBSOURCEPRICESUPP_LIVE_MODE' => Configuration::get('WEBSOURCEPRICESUPP_LIVE_MODE', true),
            'WEBSOURCEPRICESUPP_ACCOUNT_EMAIL' => Configuration::get('WEBSOURCEPRICESUPP_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'WEBSOURCEPRICESUPP_ACCOUNT_PASSWORD' => Configuration::get('WEBSOURCEPRICESUPP_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function createRoundingProduct($cts = 1)
    {
        $product = new Product();
        $product->price = $cts / 100;
        $product->active = 1;
        $product->redirect_type = '404';
        $product->indexed = 0;
        $product->is_virtual = 1;
        $product->available_for_order = 1;
        $product->visibility = 'both';
        $product->show_price = 0;
        $product->date_add = date('Y-m-d H:i:s');
        $product->date_upd = date('Y-m-d H:i:s');

        foreach (Language::getLanguages(true) as $lang) {
            $product->name[$lang['id_lang']] = 'Arrondi de ' . $cts / 100 . ' ct';
            if ($cts > 1) {
                $product->name[$lang['id_lang']] .= 's';
            }
            $product->link_rewrite[$lang['id_lang']] = 'arrondi-' . sprintf("%03d", $cts);
        }

        $product->add();

        return $product->id;
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Assets du front : registerStylesheet / registerJavascript en 1.7+ (CCC, thèmes enfants),
     * addCSS / addJS en 1.6.
     */
    public function hookDisplayHeader()
    {
        $c = $this->context->controller;
        if (method_exists($c, 'registerStylesheet')) {
            $c->registerStylesheet($this->name . '-front', 'modules/' . $this->name . '/views/css/front.css', array('media' => 'all', 'priority' => 150));
            $c->registerJavascript($this->name . '-front', 'modules/' . $this->name . '/views/js/front.js', array('position' => 'bottom', 'priority' => 150));
        } else {
            $c->addJS($this->_path . 'views/js/front.js');
            $c->addCSS($this->_path . 'views/css/front.css');
        }
    }

    /** Ancien nom du hook (PrestaShop 1.6 et installations antérieures à 1.1.0). */
    public function hookHeader()
    {
        $this->hookDisplayHeader();
    }

    /**
     * Prépare les variables du bloc « Arrondir mon panier ».
     *
     * @return bool false si aucun panier n'est disponible
     */
    protected function assignRoundingVars()
    {
        $cart = $this->context->cart;
        if (!Validate::isLoadedObject($cart)) {
            return false;
        }

        $isRounded = (int) Db::getInstance()->getValue('SELECT `is_rounded` FROM `' . _DB_PREFIX_ . 'cart` WHERE `id_cart` = ' . (int) $cart->id);

        $roundedAmount = 0;
        if ($isRounded) {
            $ids = array();
            for ($i = 1; $i < 100; $i++) {
                $id = (int) Configuration::get('WEBSOURCEPRICESUPP_PRODUCT_ROUNDING_' . sprintf('%03d', $i));
                if ($id) {
                    $ids[] = $id;
                }
            }
            if ($ids) {
                $idProduct = (int) Db::getInstance()->getValue('SELECT `id_product` FROM `' . _DB_PREFIX_ . 'cart_product` WHERE `id_cart` = ' . (int) $cart->id . ' AND `id_product` IN (' . implode(',', $ids) . ')');
                if ($idProduct) {
                    $product = new Product($idProduct);
                    $roundedAmount = (float) $product->price;
                }
            }
        }

        $this->context->smarty->assign(array(
            'ws_round_is_rounded' => $isRounded,
            'ws_round_amount' => $roundedAmount,
            'ws_round_amount_formatted' => $roundedAmount ? $this->formatPrice($roundedAmount) : '',
            'ws_round_url' => $this->context->link->getModuleLink($this->name, 'roundcart'),
            'ws_round_theme' => $this->themeFamily(),
        ));

        return true;
    }

    /**
     * PrestaShop 1.7+ : remplace checkout/cart et checkout/checkout par des gabarits du module qui
     * ÉTENDENT le gabarit du thème actif (Classic, Hummingbird, Warehouse, thème enfant...) et ajoutent
     * le bloc d'arrondi sous le récapitulatif : le balisage du thème n'est jamais recopié.
     * Un thème peut surcharger ces gabarits dans themes/<thème>/modules/websourcepricesupp/views/templates/front/.
     */
    public function hookDisplayOverrideTemplate($params)
    {
        $template_file = isset($params['template_file']) ? $params['template_file'] : '';
        if ($template_file !== 'checkout/cart' && $template_file !== 'checkout/checkout') {
            return false;
        }
        if (!$this->assignRoundingVars()) {
            return false;
        }

        if ($template_file === 'checkout/cart') {
            return 'module:' . $this->name . '/views/templates/front/override_cart.tpl';
        }

        return 'module:' . $this->name . '/views/templates/front/override_checkout.tpl';
    }

    /**
     * PrestaShop 1.6 uniquement (en 1.7+ le bloc est injecté par displayOverrideTemplate) :
     * bouton d'arrondi sous le panier. Le produit « Arrondi » faisant partie du panier,
     * le total par défaut du thème en tient compte.
     */
    public function hookDisplayShoppingCartFooter($params)
    {
        if (self::is17() || !$this->assignRoundingVars()) {
            return '';
        }

        return $this->display(__FILE__, 'hook/cart-rounding-block.tpl');
    }

    /**
     * Encart « accompagnement Websource » : uniquement dans le back-office, pour un super-administrateur
     * connecté (jamais côté boutique ni dans les e-mails). Partial : views/templates/admin/websource_support.tpl
     * (surchargeable). Seuls le nom du module, sa version, la version de PrestaShop et l'adresse du site
     * sont transmis, dans l'URL du lien, au clic.
     *
     * @return string
     */
    public function supportBlock()
    {
        $employee = $this->context->employee;
        if (!defined('_PS_ADMIN_DIR_') || !is_object($employee) || !$employee->id || !$employee->isSuperAdmin()) {
            return '';
        }
        $site = Tools::getShopDomainSsl(true);
        $base = 'https://www.websource.fr/contact?utm_source=' . rawurlencode($this->name)
            . '&utm_medium=admin-module&utm_campaign=accompagnement'
            . '&ws_module=' . rawurlencode($this->name)
            . '&ws_mv=' . rawurlencode($this->version)
            . '&ws_cms=prestashop'
            . '&ws_cmsv=' . rawurlencode(_PS_VERSION_)
            . '&ws_site=' . rawurlencode($site);
        $this->context->smarty->assign(array(
            'ws_support_contact' => $base,
            'ws_support_rdv' => $base . '&utm_content=rdv',
        ));

        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/websource_support.tpl');
    }
}
