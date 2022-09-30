<?php
/**
 * WPM Products class.
 *
 * @category   Class
 * @package    ElementorWPMProducts
 * @subpackage WordPress
 * @author     WP Maker <andrei@wpmkr.com>
 * @copyright  2022 WP Maker
 * @license    https://opensource.org/licenses/GPL-3.0 GPL-3.0-only
 * @link       link(https://www.youtube.com/channel/UClGhdRdiwZbdFqAWMVirG8g,
 *             Make an Online Store Tutorial)
 * @since      1.0.0
 * php version 7.3.9
 */

// Security Note: Blocks direct access to the plugin PHP files.
defined( 'ABSPATH' ) || die();

/**
 * Class Plugin
 *
 * WPM Product options class
 *
 * @since 1.0.0
 */
class wpm_product_options {

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 *
	 * @var Plugin The single instance of the class.
	 */
	private static $instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Plugin An instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 *  Plugin class constructor
	 *
	 * Register product options hooks and filters
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {

		add_action( 'admin_head', array( $this, 'wpm_admin_css' ) );
		add_action( 'admin_menu', array( $this, 'wpm_product_options_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'wpm_product_options_page_init' ) );

		add_action( 'wp_footer', array( $this, 'wp_footer_triggers' ) );
		add_filter( 'woocommerce_sale_flash', array( $this, 'update_sale_text' ), 15, 1 );
		add_filter( 'woocommerce_reset_variations_link', array( $this, 'update_variations_clear' ), 15, 1);
		add_filter( 'wc_add_to_cart_message_html', array( $this, 'update_atc_message' ), 15, 1);
		add_filter( 'woocommerce_get_image_size_gallery_thumbnail', array( $this, 'update_gallery_thumbnail_size' ), 15, 1);

	}

	public function wpm_product_options_add_plugin_page() {
		add_theme_page(
			'WPM Product Options', // page_title
			'WPM Options', // menu_title
			'manage_options', // capability
			'wpm-product-options', // menu_slug
			array( $this, 'wpm_product_options_create_admin_page' ) // function
		);
	}

	public function wpm_product_options_create_admin_page() {
		$this->wpm_product_options_options = get_option( 'wpm_product_options_option_name' ); ?>

		<div class="wrap">
			<h2>WP Maker Options</h2>
			<p>Please select your Store Appearance preferences below.</p>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'wpm_product_options_option_group' );
					do_settings_sections( 'wpm-product-options-admin' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function wpm_product_options_page_init() {
		register_setting(
			'wpm_product_options_option_group', // option_group
			'wpm_product_options_option_name', // option_name
			array( $this, 'wpm_product_options_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'wpm_product_options_setting_section', // id
			'', // title
			array( $this, 'wpm_product_options_section_info' ), // callback
			'wpm-product-options-admin' // page
		);

		add_settings_field(
			'change_sale_badge_text_0', // id
			'Change sale badge text?', // title
			array( $this, 'change_sale_badge_text_0_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section' // section
		);

		add_settings_field(
			'open_cart_after_add_to_cart_1', // id
			'Open cart after Add to Cart?', // title
			array( $this, 'open_cart_after_add_to_cart_1_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section' // section
		);

		add_settings_field(
			'disable_clear_2', // id
			'Disable CLEAR?', // title
			array( $this, 'disable_clear_2_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section' // section
		);

		add_settings_field(
			'high_quality_single_product_gallery_3', // id
			'High quality single product gallery?', // title
			array( $this, 'high_quality_single_product_gallery_3_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section' // section
		);

		add_settings_field(
			'remove_view_cart_popup_after_atc_4', // id
			'Remove "View Cart" popup after ATC?', // title
			array( $this, 'remove_view_cart_popup_after_atc_4_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section' // section
		);

		add_settings_field(
			'adjust_cart_icon_css_5', // id
			'Adjust cart icon CSS?', // title
			array( $this, 'adjust_cart_icon_css_5_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section' // section
		);

		add_settings_field(
			'limit_titles_to_1_line_6', // id
			'Limit titles to 1 line?', // title
			array( $this, 'limit_titles_to_1_line_6_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section' // section
		);

		add_settings_field(
			'hide_view_cart_from_minicart_7', // id
			'Remove "View Cart" from minicart?', // title
			array( $this, 'hide_view_cart_from_minicart_7_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section' // section
		);

		add_settings_field(
			'adjust_spacing_for_color_swatch_buttons_8', // id
			'Adjust spacing for color swatch buttons?', // title
			array( $this, 'adjust_spacing_for_color_swatch_buttons_8_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section' // section
		);

		add_settings_field(
			're_align_sale_badge_9', // id
			'Re-align SALE badge?', // title
			array( $this, 're_align_sale_badge_9_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section' // section
		);

		add_settings_field(
			'pagination_global_colors_10', // id
			'Pagination global colors?', // title
			array( $this, 'pagination_global_colors_10_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section' // section
		);

		add_settings_field(
			'mobile_menu_open_from_left_11', // id
			'Mobile menu open from left?', // title
			array( $this, 'mobile_menu_open_from_left_11_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section' // section
		);

		add_settings_field(
			'fix_cart_weird_jump_on_mobile_12', // id
			'Fix Cart weird jump on mobile?', // title
			array( $this, 'fix_cart_weird_jump_on_mobile_12_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section' // section
		);
	}


	public function wpm_product_options_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['change_sale_badge_text_0'] ) ) {
			$sanitary_values['change_sale_badge_text_0'] = $input['change_sale_badge_text_0'];
		}

		if ( isset( $input['open_cart_after_add_to_cart_1'] ) ) {
			$sanitary_values['open_cart_after_add_to_cart_1'] = $input['open_cart_after_add_to_cart_1'];
		}

		if ( isset( $input['disable_clear_2'] ) ) {
			$sanitary_values['disable_clear_2'] = $input['disable_clear_2'];
		}

		if ( isset( $input['high_quality_single_product_gallery_3'] ) ) {
			$sanitary_values['high_quality_single_product_gallery_3'] = $input['high_quality_single_product_gallery_3'];
		}

		if ( isset( $input['remove_view_cart_popup_after_atc_4'] ) ) {
			$sanitary_values['remove_view_cart_popup_after_atc_4'] = $input['remove_view_cart_popup_after_atc_4'];
		}

		if ( isset( $input['adjust_cart_icon_css_5'] ) ) {
			$sanitary_values['adjust_cart_icon_css_5'] = $input['adjust_cart_icon_css_5'];
		}

		if ( isset( $input['limit_titles_to_1_line_6'] ) ) {
			$sanitary_values['limit_titles_to_1_line_6'] = $input['limit_titles_to_1_line_6'];
		}

		if ( isset( $input['hide_view_cart_from_minicart_7'] ) ) {
			$sanitary_values['hide_view_cart_from_minicart_7'] = $input['hide_view_cart_from_minicart_7'];
		}

		if ( isset( $input['adjust_spacing_for_color_swatch_buttons_8'] ) ) {
			$sanitary_values['adjust_spacing_for_color_swatch_buttons_8'] = $input['adjust_spacing_for_color_swatch_buttons_8'];
		}

		if ( isset( $input['re_align_sale_badge_9'] ) ) {
			$sanitary_values['re_align_sale_badge_9'] = $input['re_align_sale_badge_9'];
		}

		if ( isset( $input['pagination_global_colors_10'] ) ) {
			$sanitary_values['pagination_global_colors_10'] = $input['pagination_global_colors_10'];
		}

		if ( isset( $input['mobile_menu_open_from_left_11'] ) ) {
			$sanitary_values['mobile_menu_open_from_left_11'] = $input['mobile_menu_open_from_left_11'];
		}

		if ( isset( $input['fix_cart_weird_jump_on_mobile_12'] ) ) {
			$sanitary_values['fix_cart_weird_jump_on_mobile_12'] = $input['fix_cart_weird_jump_on_mobile_12'];
		}

		return $sanitary_values;
	}


	public function wpm_product_options_section_info() {
		//$options = get_option( 'wpm_product_options_option_name' );
		//print_r($options);
	}

	public function change_sale_badge_text_0_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[change_sale_badge_text_0]" id="change_sale_badge_text_0" value="change_sale_badge_text_0" %s>',
			( isset( $this->wpm_product_options_options['change_sale_badge_text_0'] ) && $this->wpm_product_options_options['change_sale_badge_text_0'] === 'change_sale_badge_text_0' ) ? 'checked' : ''
		);
	}

	public function open_cart_after_add_to_cart_1_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[open_cart_after_add_to_cart_1]" id="open_cart_after_add_to_cart_1" value="open_cart_after_add_to_cart_1" %s>',
			( isset( $this->wpm_product_options_options['open_cart_after_add_to_cart_1'] ) && $this->wpm_product_options_options['open_cart_after_add_to_cart_1'] === 'open_cart_after_add_to_cart_1' ) ? 'checked' : ''
		);
	}

	public function disable_clear_2_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[disable_clear_2]" id="disable_clear_2" value="disable_clear_2" %s>',
			( isset( $this->wpm_product_options_options['disable_clear_2'] ) && $this->wpm_product_options_options['disable_clear_2'] === 'disable_clear_2' ) ? 'checked' : ''
		);
	}

	public function high_quality_single_product_gallery_3_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[high_quality_single_product_gallery_3]" id="high_quality_single_product_gallery_3" value="high_quality_single_product_gallery_3" %s>',
			( isset( $this->wpm_product_options_options['high_quality_single_product_gallery_3'] ) && $this->wpm_product_options_options['high_quality_single_product_gallery_3'] === 'high_quality_single_product_gallery_3' ) ? 'checked' : ''
		);
	}

	public function remove_view_cart_popup_after_atc_4_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[remove_view_cart_popup_after_atc_4]" id="remove_view_cart_popup_after_atc_4" value="remove_view_cart_popup_after_atc_4" %s>',
			( isset( $this->wpm_product_options_options['remove_view_cart_popup_after_atc_4'] ) && $this->wpm_product_options_options['remove_view_cart_popup_after_atc_4'] === 'remove_view_cart_popup_after_atc_4' ) ? 'checked' : ''
		);
	}

	public function adjust_cart_icon_css_5_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[adjust_cart_icon_css_5]" id="adjust_cart_icon_css_5" value="adjust_cart_icon_css_5" %s>',
			( isset( $this->wpm_product_options_options['adjust_cart_icon_css_5'] ) && $this->wpm_product_options_options['adjust_cart_icon_css_5'] === 'adjust_cart_icon_css_5' ) ? 'checked' : ''
		);
	}

	public function limit_titles_to_1_line_6_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[limit_titles_to_1_line_6]" id="limit_titles_to_1_line_6" value="limit_titles_to_1_line_6" %s>',
			( isset( $this->wpm_product_options_options['limit_titles_to_1_line_6'] ) && $this->wpm_product_options_options['limit_titles_to_1_line_6'] === 'limit_titles_to_1_line_6' ) ? 'checked' : ''
		);
	}

	public function hide_view_cart_from_minicart_7_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[hide_view_cart_from_minicart_7]" id="hide_view_cart_from_minicart_7" value="hide_view_cart_from_minicart_7" %s>',
			( isset( $this->wpm_product_options_options['hide_view_cart_from_minicart_7'] ) && $this->wpm_product_options_options['hide_view_cart_from_minicart_7'] === 'hide_view_cart_from_minicart_7' ) ? 'checked' : ''
		);
	}

	public function adjust_spacing_for_color_swatch_buttons_8_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[adjust_spacing_for_color_swatch_buttons_8]" id="adjust_spacing_for_color_swatch_buttons_8" value="adjust_spacing_for_color_swatch_buttons_8" %s>',
			( isset( $this->wpm_product_options_options['adjust_spacing_for_color_swatch_buttons_8'] ) && $this->wpm_product_options_options['adjust_spacing_for_color_swatch_buttons_8'] === 'adjust_spacing_for_color_swatch_buttons_8' ) ? 'checked' : ''
		);
	}

	public function re_align_sale_badge_9_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[re_align_sale_badge_9]" id="re_align_sale_badge_9" value="re_align_sale_badge_9" %s>',
			( isset( $this->wpm_product_options_options['re_align_sale_badge_9'] ) && $this->wpm_product_options_options['re_align_sale_badge_9'] === 're_align_sale_badge_9' ) ? 'checked' : ''
		);
	}

	public function pagination_global_colors_10_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[pagination_global_colors_10]" id="pagination_global_colors_10" value="pagination_global_colors_10" %s>',
			( isset( $this->wpm_product_options_options['pagination_global_colors_10'] ) && $this->wpm_product_options_options['pagination_global_colors_10'] === 'pagination_global_colors_10' ) ? 'checked' : ''
		);
	}

	public function mobile_menu_open_from_left_11_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[mobile_menu_open_from_left_11]" id="mobile_menu_open_from_left_11" value="mobile_menu_open_from_left_11" %s>',
			( isset( $this->wpm_product_options_options['mobile_menu_open_from_left_11'] ) && $this->wpm_product_options_options['mobile_menu_open_from_left_11'] === 'mobile_menu_open_from_left_11' ) ? 'checked' : ''
		);
	}

	public function fix_cart_weird_jump_on_mobile_12_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[fix_cart_weird_jump_on_mobile_12]" id="fix_cart_weird_jump_on_mobile_12" value="fix_cart_weird_jump_on_mobile_12" %s>',
			( isset( $this->wpm_product_options_options['fix_cart_weird_jump_on_mobile_12'] ) && $this->wpm_product_options_options['fix_cart_weird_jump_on_mobile_12'] === 'fix_cart_weird_jump_on_mobile_12' ) ? 'checked' : ''
		);
	}


 
	/**
	 * What the functions do from here on down
	 */
	function update_sale_text( $default ) {
		$wpm_product_options_options = get_option( 'wpm_product_options_option_name' );

		if( $wpm_product_options_options['change_sale_badge_text_0'] ) {
			return '<span class="onsale">SALE</span>';
		}

		return $default;
	}

	function wp_footer_triggers() {
		$wpm_product_options_options = get_option( 'wpm_product_options_option_name' );

		if ( $wpm_product_options_options['open_cart_after_add_to_cart_1'] ) {
			?>
				<script type="text/javascript">
					(function($){
						$('body').on( 'added_to_cart', function(){
							var element = document.getElementById("astra-mobile-cart-drawer");
							element.classList.add("active");
							var htmlElement = document.documentElement;
							htmlElement.classList.add("ast-mobile-cart-active");
						});
					})(jQuery);
				</script>
			<?php
		}

		// Conditional CSS styles now
		if ( $wpm_product_options_options['adjust_cart_icon_css_5'] ) {
			?><style type="text/css">
				.ast-menu-cart-outline .ast-addon-cart-wrap {
					border: none;
				}

				.ast-site-header-cart i.astra-icon {
					font-size: 1.6em;
				}

				.site-header-primary-section-right.site-header-section .ast-header-woo-cart {
					padding-right: 10px;
				}

				.ast-site-header-cart i.astra-icon:after {
					top: -9px;
					right: -9px;
					padding-top: 0.7px;
					padding-right: 0.7px;
				}
			</style><?php
		}

		if ( $wpm_product_options_options['limit_titles_to_1_line_6'] ) {
			?><style type="text/css">
				ul.products .woocommerce-loop-product__title {
					overflow: hidden;
					display: -webkit-box;
					-webkit-box-orient: vertical;
					-webkit-line-clamp: 1;
				}
			</style><?php
		}

		if ( $wpm_product_options_options['hide_view_cart_from_minicart_7'] ) {
			?><style type="text/css">
				.woocommerce-mini-cart__buttons.buttons a.wc-forward {
					display: none;
				}

				.woocommerce-mini-cart__buttons.buttons a.button.checkout.wc-forward {
					display: inline-block;
					margin-top: 0px;
				}
			</style><?php
		}

		if ( $wpm_product_options_options['adjust_spacing_for_color_swatch_buttons_8'] ) {
			?><style type="text/css">
				.theme-astra table.variations td.value {
					padding: 20px 0 20px 0 !important;
				}
			</style><?php
		}

		if ( $wpm_product_options_options['re_align_sale_badge_9'] ) {
			?><style type="text/css">
				.woocommerce.single-product .sale:not(.ast-product-gallery-layout-vertical-slider)>span.onsale {
					top: 15px;
					left: 15px;
					height: 42px;
					width: 42px;
					font-size: 12px;
				}

				.woocommerce ul.products li.product .onsale {
					left: 0;
					right: auto;
					margin: 0.8em 0 0 0.8em;
				}

				.woocommerce div.product div.images .woocommerce-product-gallery__trigger {
					width: 42px;
					height: 42px;
				}

				.woocommerce div.product div.images .woocommerce-product-gallery__trigger:before {
					top: 12px;
					left: 13px;
				}

				.woocommerce div.product div.images .woocommerce-product-gallery__trigger:after {
					top: 22px;
					left: 26px;
				}
			</style><?php
		}

		if ( $wpm_product_options_options['pagination_global_colors_10'] ) {
			?><style type="text/css">
				.woocommerce nav.woocommerce-pagination ul li {
					border: none;
				}

				.woocommerce nav.woocommerce-pagination ul li a:focus,
				.woocommerce nav.woocommerce-pagination ul li a:hover,
				.woocommerce nav.woocommerce-pagination ul li span.current {
					background-color: var(--ast-global-color-0);
				}
			</style><?php
		}

		if ( $wpm_product_options_options['mobile_menu_open_from_left_11'] ) {
			?><style type="text/css">
				.ast-mobile-popup-drawer .ast-mobile-popup-inner {
					right: auto;
					transform: translateX(-100%);
					max-width: 80%;
				}

				.ast-mobile-popup-drawer .ast-mobile-popup-header {
					justify-content: flex-start;
				}

				.ast-mobile-popup-drawer .ast-mobile-popup-header .menu-toggle-close {
					padding: 0.6em 1em;
				}

				@media (max-width: 544px) {
					.ast-mobile-popup-drawer .ast-mobile-popup-inner {
						max-width: 100%;
					}
				}
			</style><?php
		}

		if ( $wpm_product_options_options['fix_cart_weird_jump_on_mobile_12'] ) {
			?><style type="text/css">
				@media (max-width: 921px) {
					.astra-cart-drawer.active {
						transform: translateX(-80vw);
					}
					.ast-builder-menu-1 .main-header-menu,
					.ast-builder-menu-1 .main-header-menu .sub-menu,
					.ast-header-break-point .main-header-menu {
						background-color: var(--ast-global-color-5);
					}
				}

				@media (max-width: 544px) {
					.astra-cart-drawer.active {
						transform: translateX(-100vw);
					}
				}
			</style><?php
		}

	}

	function update_variations_clear( $default ) {
		$wpm_product_options_options = get_option( 'wpm_product_options_option_name' );

		if( $wpm_product_options_options['disable_clear_2'] ) {
			return '';
		}

		return $default;
	}

	function update_gallery_thumbnail_size( $default ) {
		$wpm_product_options_options = get_option( 'wpm_product_options_option_name' );

		if( $wpm_product_options_options['high_quality_single_product_gallery_3'] ) {
			return array( 'width' => 250, 'height' => 250, 'crop' => 0, );
		}

		return $default;
	}

	function update_atc_message( $default ) {
		$wpm_product_options_options = get_option( 'wpm_product_options_option_name' );

		if( $wpm_product_options_options['remove_view_cart_popup_after_atc_4'] ) {
			return false;
		}

		return $default;
	}


	// Admin CSS
	function wpm_admin_css() {
		echo '<style>
			.form-table th {
				width: 300px;
			}
		</style>';
	}


}

// Instantiate the WPM Product Options class.
wpm_product_options::instance();
