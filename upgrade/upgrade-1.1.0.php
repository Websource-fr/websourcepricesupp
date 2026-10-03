<?php
/**
 * 1.1.0 : compatibilité des thèmes Classic / Hummingbird / Warehouse.
 *  - le hook « header » (déprécié) est remplacé par displayHeader ;
 *  - displayShoppingCartFooter est enregistré (bouton d'arrondi en PrestaShop 1.6) ;
 *  - les gabarits du module étendent désormais le thème actif (plus de copie générée à l'installation).
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_0($module)
{
    $ok = true;

    // Colonne is_rounded (au cas où une version antérieure l'aurait perdue).
    $col = Db::getInstance()->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'cart` LIKE \'is_rounded\'');
    if (empty($col)) {
        $ok = $ok && Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'cart` ADD COLUMN `is_rounded` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0');
    }

    foreach (array('displayHeader', 'displayShoppingCartFooter', 'displayOverrideTemplate') as $hook) {
        if (!$module->isRegisteredInHook($hook)) {
            $ok = $module->registerHook($hook) && $ok;
        }
    }
    if ($module->isRegisteredInHook('header') && $module->isRegisteredInHook('displayHeader')) {
        $module->unregisterHook('header');
    }

    return $ok;
}
