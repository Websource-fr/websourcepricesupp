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

use Symfony\Component\Yaml\Yaml;

class WebsourcePriceSupp extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'websourcepricesupp';
        $this->tab = 'front_office_features';
        $this->version = '1.0.1';
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

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('WEBSOURCEPRICESUPP_LIVE_MODE', false);

        require_once _PS_MODULE_DIR_ . '/' . $this->name . '/sql/install.php';

        // Sélectionner le thème actif
        $select_theme = "SELECT `theme_name` FROM `" . _DB_PREFIX_ . "shop` WHERE `active` = 1";
        $results = Db::getInstance()->executeS($select_theme);

        if (!$results || empty($results)) {
            return false;
        }

        $themeName = Context::getContext()->shop->theme_name;

        $themePath = _PS_THEME_DIR_ . '../' . $themeName . '/templates/checkout/cart.tpl';

        if (!file_exists($themePath)) {
            $themeYmlPath = _PS_THEME_DIR_ . '../' . $themeName . '/config/theme.yml';
            if (file_exists($themeYmlPath)) {
                $themeConfig = Yaml::parseFile($themeYmlPath);
                if (isset($themeConfig['parent'])) {
                    $themeName = $themeConfig['parent'];
                    $themePath = _PS_THEME_DIR_ . '../' . $themeName . '/templates/checkout/cart.tpl';
                }
            }
        }

        $destinationDir = _PS_MODULE_DIR_ . $this->name . '/views/templates/front/';
        $destination = $destinationDir . 'override_cart.tpl';

        $content = file_get_contents($themePath);
        if ($content === false) {
            $this->_errors[] = $this->l('Échec de la lecture du fichier source : ') . $themePath;
            return false;
        }

        $pattern_totals = '/\{block name=\'cart_totals\'\}(.*)\{\/block\}/s';
        $replacement_totals = "{block name='cart_totals'}\n{include file='module:websourcepricesupp/views/templates/checkout/_partials/cart-detailed-totals.tpl' cart=\$cart}\n{/block}";
        $content = preg_replace($pattern_totals, $replacement_totals, $content);

        $newBlock = "{block name='cart_roundup'}\n{include file='module:websourcepricesupp/views/templates/checkout/_partials/cart-roundup.tpl' cart=\$cart}\n{/block}";
        $content = preg_replace('/(\{\/block\})/s', "$1\n$newBlock", $content, 1);

        $pattern_summary = '/\{block name=\'cart_summary\'\}(.*)\{\/block\}/s';
        $replacement_summary = "{block name='cart_summary'}\n<div class=\"card cart-summary\">\n{include file='module:websourcepricesupp/views/templates/checkout/_partials/cart-detailed-totals.tpl' cart=\$cart}\n$newBlock\n</div>\n{/block}";
        $content = preg_replace($pattern_summary, $replacement_summary, $content);

        if (file_put_contents($destination, $content) === false) {
            $this->_errors[] = $this->l('Échec de l\'écriture du fichier de destination : ') . $destination;
            return false;
        }


        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('actionValidateOrder') &&
            $this->registerHook('displayOverrideTemplate') &&
            $this->registerHook('displayBackOfficeHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName('WEBSOURCEPRICESUPP_LIVE_MODE');

        require_once _PS_MODULE_DIR_ . '/' . $this->name . '/sql/uninstall.php';

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

        return $output . $this->renderForm();
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
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'WEBSOURCEPRICESUPP_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'WEBSOURCEPRICESUPP_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'WEBSOURCEPRICESUPP_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
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

    public function hookActionValidateOrder($params)
    {
        $cart = $params['cart'];

        if($cart->is_rounded == 1) {
            $order = $params['order'];
            $order_id = $order->id;

            // ID du produit d'arrondi (doit être configuré dans votre module)
            $product_rounding_id = Configuration::get('WEBSOURCEPRICESUPP_PRODUCT_ROUNDING_ID');
            if (!$product_rounding_id) {
                // Créer le produit d'arrondi s'il n'existe pas
                $product_rounding_id = $this->createRoundingProduct();
                Configuration::updateValue('WEBSOURCEPRICESUPP_PRODUCT_ROUNDING_ID', $product_rounding_id);
            }

            // Calculer le montant pour arrondir le panier à l'entier supérieur
            $total_paid = $order->total_paid_tax_incl;
            $amount_to_round = ceil($total_paid) - $total_paid;

            if ($amount_to_round > 0) {
                // Créer un nouvel objet OrderDetail pour ajouter le produit d'arrondi à la commande
                $orderDetail = new OrderDetail();
                $orderDetail->id_order = $order_id;
                $orderDetail->product_id = $product_rounding_id;
                $orderDetail->product_name = 'Rounding Product';
                $orderDetail->product_quantity = 1;
                $orderDetail->product_price = $amount_to_round;
                $orderDetail->total_price_tax_incl = $amount_to_round;
                $orderDetail->total_price_tax_excl = $amount_to_round; // Assumer aucune taxe pour la simplicité
                $orderDetail->id_order_invoice = $order->getInvoicesCollection()->getFirst()->id;
                $orderDetail->id_shop = $this->context->shop->id;
                $orderDetail->id_warehouse = 0; // Assumer aucun entrepôt pour la simplicité

                // Enregistrer le nouveau OrderDetail
                $orderDetail->add();

                // Recalculer les totaux de la commande
                $order->total_paid_tax_incl = ceil($total_paid);
                $order->total_paid_tax_excl = ceil($total_paid); // Assumer aucune taxe pour la simplicité
                $order->total_products += $amount_to_round;
                $order->total_products_wt += $amount_to_round;
                $order->update();
            }
        }
    }

    private function createRoundingProduct()
    {
        $product = new Product();
        $product->name = array_fill_keys(Language::getIDs(), 'Arrondir mon panier');
        $product->link_rewrite = array_fill_keys(Language::getIDs(), 'arrondir-mon-panier');
        $product->price = 0;
        $product->active = 1;
        $product->redirect_type = '404';
        $product->indexed = 0;

        foreach (Language::getLanguages(true) as $lang) {
            $product->name[$lang['id_lang']] = 'Arrondir mon panier';
            $product->link_rewrite[$lang['id_lang']] = 'arrondir-mon-panier';
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
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }


    public function hookDisplayOverrideTemplate($params)
    {
        $cart = new Cart((int)$this->context->cart->id);

        $total = $cart->getOrderTotal(true, Cart::BOTH);
        $roundedTotal = ceil($total);
        $roundedAmount = $roundedTotal - $total;

        $this->context->smarty->assign(array(
            'roundedTotal' => $roundedTotal,
            'roundedAmount' => $roundedAmount,
        ));

        $template_file = $params['template_file'];

        // when I see that FrontController is trying to load 'catalog/product' I modify the behavior:
        if ($template_file === 'checkout/cart') {
            return 'module:' . $this->name . '/views/templates/front/override_cart.tpl';
        }

        // else I do nothing
        return false;
    }
}