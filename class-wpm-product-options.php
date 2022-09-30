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

			<form method="post" action="options.php" class="wpm_product_options">
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
			'wpm_product_options_setting_section_1', // id
			'Design Options', // title
			array( $this, 'wpm_product_options_section_info_1' ), // callback
			'wpm-product-options-admin' // page
		);

		add_settings_section(
			'wpm_product_options_setting_section_2', // id
			'Checkout Options', // title
			array( $this, 'wpm_product_options_section_info_2' ), // callback
			'wpm-product-options-admin' // page
		);

		add_settings_section(
			'wpm_product_options_setting_section_3', // id
			'Mobile Friendly', // title
			array( $this, 'wpm_product_options_section_info_3' ), // callback
			'wpm-product-options-admin' // page
		);

		add_settings_field(
			'improve_layout_design_1', // id
			'Improve cart icon design
			<div class="help-tip"><ul style="width: 300px;">
			<li>Adjusts spacing on product page.</li>
			<li>Improves quality of product images.</li>
			<li>Removes ugly borders around the Cart icon.</li>
			</ul></div>', // title
			array( $this, 'improve_layout_design_1_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section_1' // section
		);

		add_settings_field(
			'improve_sale_badges_2', // id
			'Improve sale badges
			<div class="help-tip"><ul style="width: 300px;">
			<li>Changes badge text from <i>Sale!</i> to <i>SALE</i>.</li>
			<li>Re-positions badge to the top-left corner.</li>
			</ul></div>', // title
			array( $this, 'improve_layout_design_1_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section_1' // section
		);

		add_settings_field(
			'enable_improved_checkout_3', // id
			'Enable improved checkout
			<div class="help-tip"><ul>
			<li>Open cart automatically after clicking <i>Add to Cart</i>.</li>
			<li>Removes <i>View Cart</i> notification after clicking <i>Add to Cart</i>.</li>
			</ul></div>', // title
			array( $this, 'improve_sale_badges_2_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section_2' // section
		);

		add_settings_field(
			'skip_over_cart_4', // id
			'Skip over the Cart page
			<div class="help-tip"><ul>
			<li>Removes the <i>View Cart</i> button from inside the slide-in cart.</li>
			</ul></div>', // title
			array( $this, 'enable_improved_checkout_3_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section_2' // section
		);

		add_settings_field(
			'mobile_menu_left_side_5', // id
			'Open mobile menu from left side
			<div class="help-tip"><ul>
			<li>By default the mobile menu opens from the right side.</li>
			<li>This will make it animate in from the left side instead.</li>
			</ul></div>', // title
			array( $this, 'skip_over_cart_4_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section_3' // section
		);

		add_settings_field(
			'product_titles_1_line_6', // id
			'Limit product titles to 1 line
			<div class="help-tip"><ul>
			<li>If you have a long product title, it can take up too much space.</li>
			<li>By enabling this, only the first line of the product title is shown.</li>
			<li><i>This is a super long product titl...</i></li>
			</ul></div>', // title
			array( $this, 'product_titles_1_line_6_callback' ), // callback
			'wpm-product-options-admin', // page
			'wpm_product_options_setting_section_3' // section
		);
	}


	public function wpm_product_options_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['improve_layout_design_1'] ) ) {
			$sanitary_values['improve_layout_design_1'] = $input['improve_layout_design_1'];
		}

		if ( isset( $input['improve_sale_badges_2'] ) ) {
			$sanitary_values['improve_sale_badges_2'] = $input['improve_sale_badges_2'];
		}

		if ( isset( $input['enable_improved_checkout_3'] ) ) {
			$sanitary_values['enable_improved_checkout_3'] = $input['enable_improved_checkout_3'];
		}

		if ( isset( $input['skip_over_cart_4'] ) ) {
			$sanitary_values['skip_over_cart_4'] = $input['skip_over_cart_4'];
		}

		if ( isset( $input['mobile_menu_left_side_5'] ) ) {
			$sanitary_values['mobile_menu_left_side_5'] = $input['mobile_menu_left_side_5'];
		}

		if ( isset( $input['product_titles_1_line_6'] ) ) {
			$sanitary_values['product_titles_1_line_6'] = $input['product_titles_1_line_6'];
		}

		return $sanitary_values;
	}


	public function wpm_product_options_section_info_1() {
		//$options = get_option( 'wpm_product_options_option_name' );
		//print_r($options);
		//print_r("Design Options");
	}

	public function wpm_product_options_section_info_2() {
		//$options = get_option( 'wpm_product_options_option_name' );
		//print_r($options);
		//print_r("Checkout Options");
	}

	public function wpm_product_options_section_info_3() {
		//$options = get_option( 'wpm_product_options_option_name' );
		//print_r($options);
		//print_r("Mobile Friendly");
	}

	public function improve_layout_design_1_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[improve_layout_design_1]" id="improve_layout_design_1" value="improve_layout_design_1" %s>',
			( isset( $this->wpm_product_options_options['improve_layout_design_1'] ) && $this->wpm_product_options_options['improve_layout_design_1'] === 'improve_layout_design_1' ) ? 'checked' : ''
		);
	}

	public function improve_sale_badges_2_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[improve_sale_badges_2]" id="improve_sale_badges_2" value="improve_sale_badges_2" %s>',
			( isset( $this->wpm_product_options_options['improve_sale_badges_2'] ) && $this->wpm_product_options_options['improve_sale_badges_2'] === 'improve_sale_badges_2' ) ? 'checked' : ''
		);
	}

	public function enable_improved_checkout_3_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[enable_improved_checkout_3]" id="enable_improved_checkout_3" value="enable_improved_checkout_3" %s>',
			( isset( $this->wpm_product_options_options['enable_improved_checkout_3'] ) && $this->wpm_product_options_options['enable_improved_checkout_3'] === 'enable_improved_checkout_3' ) ? 'checked' : ''
		);
	}

	public function skip_over_cart_4_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[skip_over_cart_4]" id="skip_over_cart_4" value="skip_over_cart_4" %s>',
			( isset( $this->wpm_product_options_options['skip_over_cart_4'] ) && $this->wpm_product_options_options['skip_over_cart_4'] === 'skip_over_cart_4' ) ? 'checked' : ''
		);
	}

	public function mobile_menu_left_side_5_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[mobile_menu_left_side_5]" id="mobile_menu_left_side_5" value="mobile_menu_left_side_5" %s>',
			( isset( $this->wpm_product_options_options['mobile_menu_left_side_5'] ) && $this->wpm_product_options_options['mobile_menu_left_side_5'] === 'mobile_menu_left_side_5' ) ? 'checked' : ''
		);
	}

	public function product_titles_1_line_6_callback() {
		printf(
			'<input type="checkbox" name="wpm_product_options_option_name[product_titles_1_line_6]" id="product_titles_1_line_6" value="product_titles_1_line_6" %s>',
			( isset( $this->wpm_product_options_options['product_titles_1_line_6'] ) && $this->wpm_product_options_options['product_titles_1_line_6'] === 'product_titles_1_line_6' ) ? 'checked' : ''
		);
	}


 
	/**
	 * What the functions do from here on down
	 */
	function update_sale_text( $default ) {
		$wpm_product_options_options = get_option( 'wpm_product_options_option_name' );

		if( $wpm_product_options_options['improve_layout_design_1'] ) {
			return '<span class="onsale">SALE</span>';
		}

		return $default;
	}

	function wp_footer_triggers() {
		$wpm_product_options_options = get_option( 'wpm_product_options_option_name' );

		if ( $wpm_product_options_options['improve_sale_badges_2'] ) {
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
		if ( $wpm_product_options_options['product_titles_1_line_6'] ) {
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

		if( $wpm_product_options_options['enable_improved_checkout_3'] ) {
			return '';
		}

		return $default;
	}

	function update_gallery_thumbnail_size( $default ) {
		$wpm_product_options_options = get_option( 'wpm_product_options_option_name' );

		if( $wpm_product_options_options['skip_over_cart_4'] ) {
			return array( 'width' => 250, 'height' => 250, 'crop' => 0, );
		}

		return $default;
	}

	function update_atc_message( $default ) {
		$wpm_product_options_options = get_option( 'wpm_product_options_option_name' );

		if( $wpm_product_options_options['mobile_menu_left_side_5'] ) {
			return false;
		}

		return $default;
	}


	// Admin CSS
	function wpm_admin_css() {
		?>
		<style>
			.form-table th {
				width: 320px;
			}

			.wpm_product_options h2 {
				margin-top: 50px;
			}

			.wpm_product_options p.submit {
				margin-top: 30px;
			}

			.help-tip li {
				list-style: square inside;
			}

			.help-tip {
				display: inline-block;
				margin: -2px 0 0 10px;
				position: absolute;
				text-align: center;
				background-color: #005c89;
				border-radius: 50%;
				width: 24px;
				height: 24px;
				font-size: 14px;
				line-height: 26px;
				cursor: default;
			}

			.help-tip:before {
				content: '?';
				display: block;
				margin-top: -1px;
				font-weight: bold;
				color: #fff;
			}

			.help-tip:hover ul {
				display: block;
				z-index: 10;
				transform-origin: 100% 0%;

				-webkit-animation: fadeIn 0.3s ease-in-out;
				animation: fadeIn 0.3s ease-in-out;
			}

			.help-tip ul {    /* The tooltip */
				display: none;
				text-align: left;
				background-color: #227299; /* #1E2021; */
				padding: 20px;
				width: 400px;
				position: absolute;
				border-radius: 3px;
				box-shadow: 1px 1px 1px rgba(0, 0, 0, 0.2);
				left: -4px;
				color: #FFF;
				font-size: 13px;
				line-height: 1.4;
			}

			.help-tip ul:before { /* The pointer of the tooltip */
				position: absolute;
				content: '';
				width: 0;
				height: 0;
				border: 6px solid transparent;
				border-bottom-color: #227299;   /* #1E2021; */
				left: 10px;
				top: -12px;
			}

			.help-tip ul:after { /* Prevents the tooltip from being hidden */
				width: 100%;
				height: 40px;
				content: '';
				position: absolute;
				top: -40px;
				left: 0;
			}

			/* CSS animation */

			@-webkit-keyframes fadeIn {
				0% { 
					opacity: 0; 
					transform: scale(0.6);
				}

				100% {
					opacity: 100%;
					transform: scale(1);
				}
			}

			@keyframes fadeIn {
				0% { opacity: 0; }
				100% { opacity: 100%; }
			}
		<?php
	}


}

// Instantiate the WPM Product Options class.
wpm_product_options::instance();
