<?php
for ($cts = 0.01; $cts < 1; $cts += 0.01) {
    $product_rounding_id = $this->createRoundingProduct($cts * 100);
    Configuration::updateValue('WEBSOURCEPRICESUPP_PRODUCT_ROUNDING_' . sprintf("%03d", $cts), $product_rounding_id);
}