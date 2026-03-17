<?php

/**
 * Admin Setting
 *
 * @package Shin
 */

namespace Zippy_Core\Src\Core;

defined('ABSPATH') or die();

class Zippy_Admin
{

	public function __construct()
	{
		//load all class in here
		$this->set_hooks();
	}

	protected function set_hooks()
	{
		//Change Footer Text in Admin

		add_filter('admin_footer_text', [$this, 'shin_change_footer_text']);

		add_action('admin_init', [$this, 'hide_admin_page_of_plugin'], 99);

		add_filter('acf/settings/show_admin', [$this, 'hide_acf_options_menu']);

		// hide site setting
		add_action('admin_init', [$this, 'hide_acf_options_menu'], 99);

		//Disable search engines from indexing this site (Only for staging domain)
		add_action('admin_init', [$this, 'disable_search_engine_indexing']);

		/*  Disable All Update Notifications with Code  */

		add_filter('pre_site_transient_update_core', [$this, 'remove_core_updates']);

		add_filter('site_transient_update_plugins', [$this, 'allow_only_zippy_updates']);

		add_filter('pre_site_transient_update_themes', [$this, 'remove_core_updates']);

		add_action('admin_enqueue_scripts', [$this, 'build_admin_scripts_and_styles']);
	}

	public function shin_change_footer_text()
	{
		echo "Core developed by <span ><a href='https://zippy.sg' target='_blank'>Zippy SG</a></span> ";
	}

	public function hide_admin_page_of_plugin()
	{
		remove_menu_page('unlimitedelements');
	}

	public function disable_search_engine_indexing()
	{
		if (!isset($_ENV['SITE_MODE']) || empty($_ENV['SITE_MODE'])) return;

		if ($_ENV['SITE_MODE'] == 'local') {
			update_option('blog_public', 0);
		} else {
			update_option('blog_public', 1);
		}
	}

	function hide_acf_options_menu()
	{

		if (!current_user_can('edit_theme_options')) {
			remove_menu_page('acf-options-site-settings');
		}
		return;
	}

	public function remove_core_updates()
	{
		global $wp_version;

		return (object) array('last_checked' => time(), 'version_checked' => $wp_version,);
	}

	public function allow_only_zippy_updates($value)
	{
		if (isset($value) && is_object($value) && isset($value->response)) {
			foreach ($value->response as $plugin_basename => $plugin_data) {
				if ($plugin_basename !== ZIPPY_CORE_BASENAME) {
					unset($value->response[$plugin_basename]);
				}
			}
		}

		return $value;
	}


	public function build_admin_scripts_and_styles()
	{
		$version = time();
		$current_user_id = get_current_user_id();

		// Pass the user ID to the script
		wp_enqueue_script('core-admin-scripts', ZIPPY_CORE_URL . '/assets/dist/js/admin.min.js', [], $version, true);
		wp_enqueue_style('core-admin-styles', ZIPPY_CORE_URL . '/assets/dist/css/admin.min.css', [], $version);

		wp_localize_script('core-admin-scripts', 'admin_id', array(
			'userID' => $current_user_id,
		));

		wp_localize_script('core-admin-scripts', 'core_admin_api', array(
			'url' => esc_url_raw(rest_url(ZIPPY_CORE_API_PREFIX)),
			'nonce'  => wp_create_nonce('wp_rest'),
		));
	}
}
