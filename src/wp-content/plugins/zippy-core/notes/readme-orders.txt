
1. Active Zippy Orders page: 
- Woocommerce / Zippy - Woo
- Check "Enable customize orders page"
- Save Changes
* options key: zippy_woo_custom_orders_enabled => yes || no

Example postman : 
Get: {{domain}}/wp-json/{{api_url_prefix}}/orders
raw: {
    "page": 2,
    "per_page": 10
}


2. example api:
fetch(`${core_admin_api.url}/orders`, {
  headers: {
    'X-WP-Nonce': core_admin_api.nonce,
  },
})
  .then((r) => r.json())
  .then((data) => console.log(data));
