{**
 * Bloc « Arrondir mon panier » : balisage volontairement minimal et compatible
 * Bootstrap 3 (PrestaShop 1.6), Bootstrap 4 (Classic, Warehouse) et Bootstrap 5 (Hummingbird).
 * Surchargeable depuis le thème : themes/<thème>/modules/websourcepricesupp/views/templates/hook/cart-rounding-block.tpl
 *}
<div class="ws-cart-rounding ws-theme-{$ws_round_theme|default:'other'|escape:'html':'UTF-8'}">
  {if $ws_round_is_rounded && $ws_round_amount_formatted}
    <p class="ws-cart-rounding__line">
      <span class="ws-cart-rounding__label">{l s='Montant arrondi ajouté :' mod='websourcepricesupp'}</span>
      <strong class="ws-cart-rounding__value">{$ws_round_amount_formatted|escape:'html':'UTF-8'}</strong>
    </p>
  {/if}
  <button type="button"
          class="{if $ws_round_theme == '16'}button btn btn-default button-medium{else}btn btn-primary{/if} btn-cart-rounding ws-cart-rounding__btn"
          data-round="{$ws_round_url|escape:'html':'UTF-8'}">
    {if $ws_round_is_rounded}{l s='Ne plus arrondir mon panier' mod='websourcepricesupp'}{else}{l s='Arrondir mon panier' mod='websourcepricesupp'}{/if}
  </button>
</div>
