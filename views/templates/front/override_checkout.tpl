{extends file=$layout}

{block name='content'}
    <section id="main">
        <div class="cart-grid mb-3 row">
            <div class="cart-grid-body col-12 col-lg-8 mb-4">
                <div class="checkout-step-order box-bg js-checkoutStepOrderBox"></div>
                <div class="checkout-step-display">
                    {render file='checkout/checkout-process.tpl' ui=$checkout_process}
                </div>
            </div>

            <div class="cart-grid-right col-12 col-lg-4 mb-4">
                <div class="cart-items light-box-bg cart-summary">
                    <div class="cart-rounding-flex">
                    {include file='module:websourcepricesupp/views/templates/checkout/_partials/cart-detailed-totals.tpl' cart=$cart}
                    {block name='cart_roundup'}
                        {include file='module:websourcepricesupp/views/templates/checkout/_partials/cart-roundup.tpl' cart=$cart}
                    {/block}
                    </div>

                    <div class="js-cart-update-voucher page-loading-overlay cart-overview-loading">
                        <div class="page-loading-backdrop d-flex align-items-center justify-content-center">
                            <span class="uil-spin-css"><span><span></span></span><span><span></span></span><span><span></span></span><span><span></span></span><span><span></span></span><span><span></span></span><span><span></span></span><span><span></span></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {block name='hook_shopping_cart_footer'}{/block}
    </section>
{/block}

{block name='header'}
    {include file='checkout/_partials/header.tpl'}
{/block}

{block name="footer"}
    {include file="checkout/_partials/footer.tpl"}
{/block}

{block name='breadcrumb'}{/block}
{block name='hook_outside_main_page'}{/block}
{block name='st_menu_left'}{/block}
{block name='st_menu_right'}{/block}
