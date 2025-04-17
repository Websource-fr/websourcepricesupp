<div class="cart-rounding">
    <div class="cart-rounding-button-container text-center">
        <a href="javascript:void(0);" class="btn btn-primary btn-cart-rounding"
           data-round="{$link->getModuleLink('websourcepricesupp', 'roundcart')}">
            {if Context::getContext()->cart->is_rounded == 0}
                {l s='Arrondir mon panier'  mod='websourcepricesupp'}
            {else}
                {l s='Ne plus arrondir mon panier'  mod='websourcepricesupp'}
            {/if}
        </a>
    </div>
</div>