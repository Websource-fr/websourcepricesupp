<div class="card-body cart-summary-totals js-cart-summary-totals">

    {block name='cart_summary_total'}
        {if !$configuration.display_prices_tax_incl && $configuration.taxes_enabled}
            <div class="cart-summary-line">
                <span class="label">{$cart.totals.total.label}&nbsp;{$cart.labels.tax_short}</span>
                <span class="value">{$cart.totals.total.value}</span>
            </div>
            {block name='websource_cart_roundup'}
                {include file='module:websourcepricesupp/views/templates/hook/cart_rounding.tpl' cart=$cart}
            {/block}
            <div class="cart-summary-line cart-total">
                <span class="label">{$cart.totals.total_including_tax.label}</span>
                {if Context::getContext()->cart->is_rounded == 1}
                    <span class="value">{Tools::displayPrice($roundedTotal, Context::getContext()->currency)}</span>
                {/if}
                {if Context::getContext()->cart->is_rounded == 0}
                    <span class="value">{Tools::displayPrice($cart.totals.total_including_tax.value, Context::getContext()->currency)}</span>
                {/if}

            </div>
        {else}
            {block name='websource_cart_roundup'}
                {include file='module:websourcepricesupp/views/templates/hook/cart_rounding.tpl' cart=$cart}
            {/block}

            <div class="cart-summary-line cart-total">
                <span class="label">{$cart.totals.total.label}&nbsp;{if $configuration.display_taxes_label && $configuration.taxes_enabled}{$cart.labels.tax_short}{/if}</span>
                <span class="value">{$cart.totals.total.value}</span>
            </div>
        {/if}
    {/block}


    {block name='cart_summary_tax'}
        {if $cart.subtotals.tax}
            <div class="cart-summary-line">
                <span class="label sub">{l s='%label%:' sprintf=['%label%' => $cart.subtotals.tax.label] d='Shop.Theme.Global'}</span>
                <span class="value sub">{$cart.subtotals.tax.value}</span>
            </div>
        {/if}
    {/block}

    {hook h='displayCartAjaxInfo'}

</div>