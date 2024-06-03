{assign var='formattedRoundedAmount' value=Tools::displayPrice($roundedAmount, Context::getContext()->currency)}

{if Context::getContext()->cart->is_rounded == 1}
    <div class="row is-rounded">
        <div class="col">
            <div class="cart-summary-line cart-total is-rounded">
                <span class="label">{l s='Montant arrondi ajouté:' mod='websourcepricesupp'}</span>
                <span class="value">{$formattedRoundedAmount}</span>
            </div>
        </div>
    </div>
{/if}