{**
 * Page panier (PrestaShop 1.7+) : étend le gabarit du thème actif (Classic, Hummingbird, Warehouse,
 * thème enfant...) et ajoute le bloc d'arrondi sous le récapitulatif. Rien du balisage du thème n'est recopié.
 *}
{extends file='checkout/cart.tpl'}

{block name='cart_totals'}
  {$smarty.block.parent}
  {include file='module:websourcepricesupp/views/templates/hook/cart-rounding-block.tpl'}
{/block}
