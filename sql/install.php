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

$sql = array();

// Vérifier si la colonne 'is_rounded' existe déjà
$sql_check_column = "SHOW COLUMNS FROM `"._DB_PREFIX_."cart` LIKE 'is_rounded'";
$column_exists = Db::getInstance()->executeS($sql_check_column);

if (empty($column_exists)) {
    $sql[] = "ALTER TABLE `"._DB_PREFIX_."cart` ADD COLUMN `is_rounded` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0'";
}

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}