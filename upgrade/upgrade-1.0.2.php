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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2024 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * This function updates your module from previous versions to the version 1.1,
 * usefull when you modify your database, or register a new hook ...
 * Don't forget to create one file per version.
 */
function upgrade_module_1_0_2($module)
{
    for ($cts = 0.01; $cts < 1; $cts += 0.01) {
        $product = new Product();
        $product->name = array_fill_keys(Language::getIDs(), 'Arrondi ' . $cts/100);
        $product->link_rewrite = array_fill_keys(Language::getIDs(), 'donation-' . $cts);
        $product->price = $cts/100;
        $product->active = 1;
        $product->redirect_type = '404';
        $product->indexed = 0;

        foreach (Language::getLanguages(true) as $lang) {
            $product->name[$lang['id_lang']] = 'Arrondi ' . $cts/100;
            $product->link_rewrite[$lang['id_lang']] = 'arrondi-' . $cts;
        }

        $product->add();

        $product_rounding_id = $product->id;

        Configuration::updateValue('WEBSOURCEPRICESUPP_PRODUCT_ROUNDING_' . sprintf("%03d", $cts), $product_rounding_id);
    }
}
