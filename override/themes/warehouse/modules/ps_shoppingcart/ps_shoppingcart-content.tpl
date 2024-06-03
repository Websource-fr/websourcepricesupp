<div id="_desktop_blockcart-content" class="dropdown-menu-custom dropdown-menu">
    <div id="blockcart-content" class="blockcart-content" >
        <div class="cart-title">
            <span class="modal-title">{l s='Shopping Cart' d='Shop.Theme.Checkout'}</span>
            <button type="button" id="js-cart-close" class="close">
                <span>×</span>
            </button>
            <hr>
        </div>
        {if isset($cart.products) && $cart.products}
            <ul class="cart-products">
                {foreach from=$cart.products item=product}
                    <li>{include 'module:ps_shoppingcart/ps_shoppingcart-product-line.tpl' product=$product}</li>
                {/foreach}
            </ul>

            <div class="cart-subtotals">
                {foreach from=$cart.subtotals item="subtotal"}
                    {if (isset($subtotal.value) && $subtotal.value) && (isset($subtotal.type) && $subtotal.type !== 'tax')}
                        <div class="cart-summary-line" id="cart-subtotal-{$subtotal.type}">
          <span class="label{if 'products' === $subtotal.type} js-subtotal{/if}">
            {if 'products' == $subtotal.type}
                {$cart.summary_string}
            {else}
                {$subtotal.label}
            {/if}
          </span>
                            <span class="value">
            {if 'discount' == $subtotal.type}-&nbsp;{/if}{$subtotal.value}
          </span>
                            {if $subtotal.type === 'shipping'}
                                <div><small class="value">{hook h='displayCheckoutSubtotalDetails' subtotal=$subtotal}</small></div>
                            {/if}
                        </div>
                    {/if}
                {/foreach}
            </div>

            <div class="cart-totals">

                {if isset($configuration.display_prices_tax_incl)}
                    {if !$configuration.display_prices_tax_incl && $configuration.taxes_enabled}
                        <div class="clearfix text-default">
                            <span class="label">{$cart.totals.total.label}&nbsp;{$cart.labels.tax_short}</span>
                            <span class="value float-right">{$cart.totals.total.value}</span>
                        </div>
                        <div class="clearfix">
                            <span class="label">{$cart.totals.total_including_tax.label}</span>
                            <span class="value float-right">{$cart.totals.total_including_tax.value}</span>
                        </div>
                    {else}
                        <div class="clearfix">
                            <span class="label">{$cart.totals.total.label}&nbsp;{if $configuration.taxes_enabled}{$cart.labels.tax_short}{/if}</span>
                            <span class="value float-right">{$cart.totals.total.value}</span>
                        </div>
                    {/if}
                {else}
                    <div class="clearfix">
                        <span class="label">{$cart.totals.total.label} {$cart.labels.tax_short}</span>
                        <span class="value float-right">{$cart.totals.total.value}</span>
                    </div>
                {/if}

            </div>
            {hook h='displayCartAjaxInfo'}
            {hook h='displayCartAjaxInfoBlock'}
            <div class="cart-buttons text-center">
                {if $cart.products_count > 0}
                    {if $cart.minimalPurchaseRequired}
                        <div class="alert alert-warning" role="alert">
                            {$cart.minimalPurchaseRequired}
                        </div>
                    {else}
                        <a href="{url entity=order}"
                           class="btn btn-primary btn-block btn-lg mb-2">{l s='Checkout' d='Shop.Theme.Actions'}</a>
                    {/if}
                    <a rel="nofollow" class="btn btn-secondary btn-block"
                       href="{$cart_url}">{l s='Cart' d='Shop.Theme.Checkout'}</a>
                {/if}
            </div>
        {else}
            <span class="no-items">{l s='There are no more items in your cart' d='Shop.Theme.Checkout'}</span>
        {/if}
    </div>
</div>