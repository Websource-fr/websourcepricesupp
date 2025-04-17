<?php

class WebsourcePriceSuppRoundCartModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function initContent()
    {
        parent::initContent();

        // Rediriger vers la page du panier
        Tools::redirect('index.php?controller=cart');
    }

    public function postProcess()
    {
        parent::postProcess();

        $cart = $this->context->cart;
        $cart_id = $cart->id;
        if (!Validate::isLoadedObject($cart)) {
            die(json_encode(['success' => false]));
        }

        for ($cts = 1; $cts < 100; $cts++) {
            if (Configuration::get('WEBSOURCEPRICESUPP_PRODUCT_ROUNDING_' . sprintf("%03d", $cts)) == null) {
                $product_rounding_id = $this->module->createRoundingProduct($cts);
                Configuration::updateValue('WEBSOURCEPRICESUPP_PRODUCT_ROUNDING_' . sprintf("%03d", $cts), $product_rounding_id);
            }
        }

        // Calculer le montant pour arrondir le panier à l'entier supérieur
        $total_paid = $cart->getOrderTotal(true, Cart::BOTH);
        $amount_to_round = ceil((double)$total_paid) - (double)$cart->getOrderTotal(true, Cart::BOTH);


        // Mettre à jour le prix du produit e nfonction du total du panier
        if ($cart->is_rounded == 0) {
            $new_text = $this->l('Ne plus arrondir mon panier');
            $cart->is_rounded = 1;

            $sql = "INSERT INTO " . _DB_PREFIX_ . "cart_product SET 
            id_cart=" . $cart->id . ",
            id_product=" . (int)Configuration::get('WEBSOURCEPRICESUPP_PRODUCT_ROUNDING_' . sprintf('%03d', round($amount_to_round * 100))) . ",
            id_address_delivery=" . $cart->id_address_delivery . ",
            id_shop=" . $cart->id_shop . ",
            id_product_attribute=0,
            id_customization=0,
            quantity=1,
            date_add=NOW()
            ";

            Db::getInstance()->execute($sql);

        } else {
            $new_text = $this->l('Arrondir mon panier');
            $cart->is_rounded = 0;

            for ($i = 1; $i < 100; $i++) {

            }
            for ($i = 1; $i < 100; $i++) {
                $id_product_to_delete = (int)Configuration::get('WEBSOURCEPRICESUPP_PRODUCT_ROUNDING_' . sprintf('%03d', $i));
                $cart->deleteProduct((int)$id_product_to_delete);
                $sql = "DELETE FROM " . _DB_PREFIX_ . "cart_product WHERE id_cart=" . $cart->id . " AND  id_product=" . $id_product_to_delete;
                Db::getInstance()->execute($sql);
            }

        }

        $sql = "UPDATE " . _DB_PREFIX_ . "cart SET is_rounded = " . $cart->is_rounded . " WHERE id_cart = " . $cart->id;
        Db::getInstance()->execute($sql);

        die(json_encode(['success' => true, 'state' => $cart->is_rounded, 'new_text' => $new_text]));
    }
}
