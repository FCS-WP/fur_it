jQuery(function($) {
    const ordersLink = $('#toplevel_page_woocommerce a[href$="wc-orders"]');
    if (ordersLink) ordersLink.hide();
})