<?php

/**
 * Optimise theme
 *
 * @package Shin
 */

namespace Zippy_Core\Src\Core;

defined('ABSPATH') or die();

class Zippy_Optimise
{

	public function __construct()
	{
		//load all class in here
		$this->set_hooks();
	}

	protected function set_hooks()
	{
		add_filter('script_loader_tag', [$this, 'add_defer_attribute'], 10, 2);

		add_filter('script_loader_tag', [$this, 'add_async_attribute'], 10, 2);

		add_action('wp_enqueue_scripts', [$this, 'remove_block_css'], 100);


		remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
		remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
	}


	public function add_defer_attribute($tag, $handle)
	{
		// add script handles to the array below
		$scripts_to_defer = array('main-scripts-js', '');

		foreach ($scripts_to_defer as $defer_script) {
			if ($defer_script === $handle) {
				return str_replace(' src', ' defer src', $tag);
			}
		}

		return $tag;
	}

	public function add_async_attribute($tag, $handle)
	{
		// add script handles to the array below
		$scripts_to_async = array('formidable-js', '');

		foreach ($scripts_to_async as $async_script) {
			if ($async_script === $handle) {
				return str_replace(' src', ' async src', $tag);
			}
		}

		return $tag;
	}


	public function remove_block_css()
	{
		wp_dequeue_style('wp-block-library'); // Wordpress core
		wp_dequeue_style('wp-block-library-theme'); // Wordpress core
		wp_dequeue_style('wc-block-style'); // WooCommerce
		wp_dequeue_style('storefront-gutenberg-blocks'); // Storefront theme
	}
}
