<?php
class Cart extends CartCore {
    public $is_rounded = 0;

    public function __construct($id = null, $idLang = null)
    {
        parent::__construct($id, $idLang);

        self::$definition['fields']['is_rounded'] = ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'];
    }
}