<?php
/*
   Plugin Name: WpMuBar Free
   Description: A plugin that include global navigation bar for Worpdress Multisite
   Version: 1.0
   Author: Frumatic
   Author URI: http://frumatic.com
   License: GPL2
   Network: true;
   */

$opt = get_site_option('wpmubar_options');


class WpMuBarFree
{

	/**
	 * Constructor
	 *
	 * Registers the plugin capabilities, admin menus,
	 * as well as the set of actions and filters used by WpMuBar.
	 *
	 * $this->options is used to store all the theme options, while
	 * $this->defaults holds their default values.
	 *
	 */
	function __construct()
	{

		// Set the plugin path url
		$this->url_path = WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__));

		if (!is_admin()) {
			wp_enqueue_script('selectBox_script', plugins_url('/js/jquery.selectBox.min.js', __FILE__), array('jquery'), '1.5');
			wp_register_style('selectBox_style', plugins_url('/css/jquery.selectBox.css', __FILE__));
			wp_register_style('reset', plugins_url('/css/reset.css', __FILE__));
			wp_enqueue_style('wpmubar', plugins_url('/css/wpmubar.css', __FILE__), array('selectBox_style', 'reset'));
		}
		add_action('wp_footer', array(&$this, 'wpmubar_frontend'));

	}

	function myplugin_deactivate()
	{
		set_site_transient('update_plugins', null);
	}

	function wp_list_sites($expires = 7200)
	{
		if (!is_multisite()) return false;

		// Because the get_blog_list() function is currently flagged as deprecated
		// due to the potential for high consumption of resources, we'll use
		// $wpdb to roll out our own SQL query instead. Because the query can be
		// memory-intensive, we'll store the results using the Transients API
		if (false === ($site_list = get_transient('multisite_site_list'))) {
			global $wpdb;
			$site_list = $wpdb->get_results($wpdb->prepare('SELECT * FROM wp_blogs ORDER BY blog_id'));
			// Set the Transient cache to expire every two hours
			set_site_transient('multisite_site_list', $site_list, $expires);
		}

		$current_site_url = get_site_url(get_current_blog_id());

		foreach ($site_list as $site) {
			switch_to_blog($site->blog_id);
			$class = (home_url() == $current_site_url) ? ' class="current-site-item"' : '';
			$html[] = array("blog_id" => $site->blog_id, "domain" => home_url(), "path" => "");
			restore_current_blog();
		}

		return $html;
	}

	function wpmubar_frontend()
	{
		global $opt;
		?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$("SELECT").selectBox();
				$("#bar-close").click(function () {
					$(".global-menu-wrapper").slideToggle("medium");
					$("body").animate({'border-spacing':0, marginTop:0}, {step:function (now, fx) {
						$(fx.elem).css("background-position", "0px " + now + "px");
					}, duration:"medium"}, function () {
					});
					$(".label-bar").slideToggle("medium");
				});
				$(".label-bar").click(function () {
					$(".global-menu-wrapper").slideToggle("medium");
					if ($('#login').hasClass('global-menu-wrapper eleven') || $('#login').hasClass('global-menu-wrapper login eleven'))
						$("body").animate({'border-spacing':60, marginTop:88}, {step:function (now, fx) {
							$(fx.elem).css("background-position", "0px " + now + "px");
						}, duration:"medium"});
					else
						$("body").animate({'border-spacing':60, marginTop:60}, {step:function (now, fx) {
							$(fx.elem).css("background-position", "0px " + now + "px");
						}, duration:"medium"});
					$(".label-bar").slideToggle("medium");
				});
			});
		</script>
		<?php
			if (is_user_logged_in()) {
				?>
			<style type="text/css" media="screen">html body {
				margin-top: 60px;
				background-position: 0 60px;
			}

			.label-bar {
				margin-top: 28px;
			}</style>
			<?php
			}
			else {
				?>
			<style type="text/css" media="screen">html body {
				margin-top: 60px;
				background-position: 0 60px;
			}</style>
			<?php
			}
			if ('Twenty Eleven' == wp_get_theme()) {
				$theme = 'eleven';
				?>
			<style type="text/css" media="screen">html body {
				margin-top: 88px;
				background-position: 0 60px;
			}</style>
			<?php
			}

			global $t, $opt;
			// Frontend html here
			?>
		<div class="global-menu-wrapper <?php  if (is_user_logged_in()) echo 'login '; echo $theme; ?>" id="login">
			<ul id="global-menu">
				<li class="global-menu-logo"><a href="http://store.theme.fm/wpmubar"><img
					src="<?php echo $this->url_path . '/images/button.gif'; ?>"></a></li>
				<li class="theme-selector">
					<select
						onchange="if(this.options[this.selectedIndex].value != ''){ window.top.location.href = this.options[this.selectedIndex].value }">
						<?php
						$i = 0;
						$blogs = $this->wp_list_sites();
						$selected = '';
						foreach ($blogs as $key) {
							$name_ = get_blog_details($key['blog_id']);
							if (get_bloginfo('name') == $name_->blogname) { //if url themes and current url are same
								$selected = 'selected';
								$blog_id = $name_->blog_id;
							}
							?>
							<option
								value="<?php echo $key['domain'] . $key['path']; ?>" <?php echo $selected; $selected = ''; ?>><?php echo  $name_->blogname; ?></option>
							<?php //}
						}  ?>
					</select>
				</li>

				<li class="global-menu-social"></li>
				<li class="global-menu-close">
					<a id="bar-close" href="javascript:;"></a>
				</li>
			</ul>
		</div>
		<div class="label-bar"></div>
		<?php
	}
}

function WpMuBarFree()
{
	global $WpMuBar;
	$WpMuBar = new WpMuBarFree();
}

add_action('init', 'WpMuBarFree');
?>