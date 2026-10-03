{**
 * Page de commande (PrestaShop 1.7+) : étend le gabarit du thème actif et ajoute le bloc d'arrondi
 * sous le récapitulatif du panier.
 *}
{extends file='checkout/checkout.tpl'}

{block name='cart_summary'}
  {$smarty.block.parent}
  {include file='module:websourcepricesupp/views/templates/hook/cart-rounding-block.tpl'}
{/block}
