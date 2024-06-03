{if Context::getContext()->cart->is_rounded == 1}
    <div class="row is-rounded">
        <div class="col">
            <div class="cart-summary-line cart-total is-rounded">
                <span class="label">{l s='Montant arrondi :' d='Shop.Theme.Global'}</span>
                <span class="value">{Tools::displayPrice($roundedAmount, Context::getContext()->currency)}</span>
            </div>
        </div>
    </div>
{/if}