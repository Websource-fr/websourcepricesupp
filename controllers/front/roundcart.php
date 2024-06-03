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
        if (!Validate::isLoadedObject($cart)) {
            die(json_encode(['success' => false]));
        }

        if($cart->is_rounded === 0) {
            $new_text = $this->l('Ne plus arrondir mon panier');
            $cart->is_rounded = 1;
        } else {
            $new_text = $this->l('Arrondir mon panier');
            $cart->is_rounded = 0;
        }

        $cart->save();
        $sql = "UPDATE ". _DB_PREFIX_ ."cart SET is_rounded = ". $cart->is_rounded ." WHERE id_cart = ". $cart->id;
        Db::getInstance()->execute($sql);

        die(json_encode(['success' => true, 'state' => $cart->is_rounded, 'new_text' => $new_text]));
    }
}
