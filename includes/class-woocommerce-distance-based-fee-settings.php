<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WooCommerce_Distance_Based_Fee_Settings {

	/**
	 * The single instance of WooCommerce_Distance_Based_Fee_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'wc_distance_fee_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		$page = add_options_page( __( 'Distance based fee settings', 'woocommerce-distance-based-fee' ) , __( 'Distance based fee settings', 'woocommerce-distance-based-fee' ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

    	// We're including the WP media scripts here because they're needed for the image upload field
    	// If you're not including an image upload then you can leave this function call out
    	wp_enqueue_media();

    	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
    	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Distance based fee settings', 'woocommerce-distance-based-fee' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$shipping_methods = WC()->shipping->load_shipping_methods();

		function getFeeBasedIds($shipping_methods) {
			$ids = array();
			foreach ($shipping_methods as $i=>$v) {
				array_push($ids, array('id' => $v->id, 'method_title' => $v->method_title ) );
			}
			return $ids;
		}
		
		$shippingIds = getFeeBasedIds($shipping_methods);

		$settings['standard'] = array(
			'title'					=> __( 'Standard', 'woocommerce-distance-based-fee' ),
			'description'			=> __( 'Standard settings. If you cannot see any change on the settings at the WooCommerce checkout page, please save your shipping methods options also <a target="_blank" href="' . admin_url() . 'admin.php?page=wc-settings&tab=shipping">here</a>!', 'woocommerce-distance-based-fee' ),
			'fields'				=> array(
				array(
					'id' 			=> 'google_api_key',
					'label'			=> __( 'Google API key' , 'woocommerce-distance-based-fee' ),
					'description'	=> __( 'Enter your Google API key in this field. You can get your API key <a target="_blank" href="https://developers.google.com/maps/documentation/javascript/get-api-key">from here.</a>', 'woocommerce-distance-based-fee' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'fee_name',
					'label'			=> __( 'Fee name' , 'woocommerce-distance-based-fee' ),
					'description'	=> __( 'Enter name for the fee that customer will see on checkout.', 'woocommerce-distance-based-fee' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'divider',
					'label'			=> __( 'Divider' , 'woocommerce-distance-based-fee' ),
					'description'	=> __( 'Enter your divider number here. Price will be calculated by dividing the distance, and then multiplicated by the price.', 'woocommerce-distance-based-fee' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'price',
					'label'			=> __( 'Price' , 'woocommerce-distance-based-fee' ),
					'description'	=> __( 'Enter your price for the calculated distance.', 'woocommerce-distance-based-fee' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'minimum_distance',
					'label'			=> __( 'Minimun distance' , 'woocommerce-distance-based-fee' ),
					'description'	=> __( 'Enter the distance after the fee will be added. If distance is lower than this, only the shipping costs will be used without a distance based fee. Leave empty to disable this feature.', 'woocommerce-distance-based-fee' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'maximum_distance',
					'label'			=> __( 'Maximum distance' , 'woocommerce-distance-based-fee' ),
					'description'	=> __( 'Enter the maximum distance for the shipping method, that can be used with the fee. Shipping method hiding options will be used if the distance is more than the limit set here. Leave empty to disable this feature.', 'woocommerce-distance-based-fee' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'minimum_cart_price',
					'label'			=> __( 'Minimum cart total price' , 'woocommerce-distance-based-fee' ),
					'description'	=> __( 'Enter the minimun cart total price for the shipping method, that can be used with the fee. If cart price is lower than this, shipping method hiding options will be used. Leave empty to disable this feature.', 'woocommerce-distance-based-fee' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'units',
					'label'			=> __( 'Units' , 'woocommerce-distance-based-fee' ),
					'description'	=> __( 'Choose your units for the calculated distance.', 'woocommerce-distance-based-fee' ),
					'type'			=> 'select',
					'options'		=> array('km' => 'Kilometers', 'ml' => 'Miles'),
					'default'		=> 'km',
				),
				array(
					'id' 			=> 'methods',
					'label'			=> __( 'Methods', 'woocommerce-distance-based-fee' ),
					'description'	=> __( 'Choose which shipping methods this fee is added for.', 'woocommerce-distance-based-fee' ),
					'type'			=> 'select_multi',
					'class'         => 'wc-enhanced-select',
					'options'       => $shippingIds,
					'default'		=> array()
				),
				array(
					'id' 			=> 'disable_shippings',
					'label'			=> __( 'Shipping conditions' , 'woocommerce-distance-based-fee' ),
					'description'	=> __( 'If above conditions are not met, what will happen to the shipping? Please note!! After changing this option, you also need to save your <a target="_blank" href="' . admin_url() . 'admin.php?page=wc-settings&tab=shipping">shipping method´s settings</a> once, in order the changes to take effect!', 'woocommerce-distance-based-fee' ),
					'type'			=> 'select',
					'options'		=> array('hide' => 'Hide shipping method(s)', 'not_hide' => 'Don´t hide the shipping methods', 'fixed' => 'Use fixed fee (enter below)'),
					'default'		=> 'km',
				),
				array(
					'id' 			=> 'fixed_fee',
					'label'			=> __( 'Fixed fee' , 'woocommerce-distance-based-fee' ),
					'description'	=> __( 'If above conditions are not met, fixed fee can be used. Leave empty to disable this feature.', 'woocommerce-distance-based-fee' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'taxable',
					'label'			=> __( 'Taxable', 'woocommerce-distance-based-fee' ),
					'description'	=> __( 'Is fee taxable?', 'woocommerce-distance-based-fee' ),
					'type'			=> 'select',
					'options'		=> array( 'no' => __('No', 'woocommerce-distance-based-fee'), 'yes' => __('Yes', 'woocommerce-distance-based-fee') ),
					'default'		=> 'yes'
				),
				array(
					'id' 			=> 'disable_virtual',
					'label'			=> __( 'Disable virtual products' , 'woocommerce-distance-based-fee' ),
					'description'	=> __( 'Disable on virtual products?', 'woocommerce-distance-based-fee' ),
					'type'			=> 'select',
					'options'		=> array('no' => 'No', 'all_virtual' => 'Disable only if all products are virtual on the cart', 'yes' => 'Disable if any of the products are virtual on the cart'),
					'default'		=> 'no',
				),
				array(
					'id' 			=> 'to_address',
					'label'			=> __( 'Destination address' , 'woocommerce-distance-based-fee' ),
					'description'	=> __( 'Choose if destination should be calculated by billing or shipping address. By default, shipping address will be used.', 'woocommerce-distance-based-fee' ),
					'type'			=> 'select',
					'options'		=> array('shipping' => 'Shipping address', 'billing' => 'Billing address'),
					'default'		=> 'shipping',
				),
				array(
					'id' 			=> 'origin_address',
					'label'			=> __( 'From address' , 'woocommerce-distance-based-fee' ),
					'description'	=> __( 'Enter the from address, where the distance will be calucalted. If empty, the address set in WooCommerce settings will be used. Make also sure your address can be discovered by the Google API. You can try to locate your address at Google Maps service: <a href="https://www.google.com/maps" target="_blank">https://www.google.com/maps</a>', 'woocommerce-distance-based-fee' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'origin_zip',
					'label'			=> __( 'From zipcode' , 'woocommerce-distance-based-fee' ),
					'description'	=> __( 'Enter the from zipcode, where the distance will be calucalted. If empty, the zipcode set in WooCommerce settings will be used.', 'woocommerce-distance-based-fee' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
				array(
					'id' 			=> 'origin_city',
					'label'			=> __( 'From city' , 'woocommerce-distance-based-fee' ),
					'description'	=> __( 'Enter the from city, where the distance will be calucalted. If empty, the city set in WooCommerce settings will be used.', 'woocommerce-distance-based-fee' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> ''
				),
			)
		);

		$settings['logs'] = array(
			'title'					=> __( 'Logs', 'woocommerce-distance-based-fee' ),
			'description'			=> __( 'Plugin log file', 'woocommerce-distance-based-fee' ),
			'fields'				=> array(
				array(
					'id' 			=> 'google_api_errors',
					'label'			=> __( 'Google API log' , 'woocommerce-distance-based-fee' ),
					'description'	=> __( 'If your fee won´t work, you can check if Google API has given any error messages.', 'woocommerce-distance-based-fee' ),
					'type'			=> 'textarea-disable',
					'default'		=> '',
					'placeholder'	=> ''
				),
				
			)
		);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			$html .= '<h2>' . __( 'Distance based fee settings' , 'woocommerce-distance-based-fee' ) . '</h2>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields
				ob_start();
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				$html .= ob_get_clean();

				$html .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'woocommerce-distance-based-fee' ) ) . '" />' . "\n";
				$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";

		echo $html;
	}

	/**
	 * Main WooCommerce_Distance_Based_Fee_Settings Instance
	 *
	 * Ensures only one instance of WooCommerce_Distance_Based_Fee_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WooCommerce_Distance_Based_Fee()
	 * @return Main WooCommerce_Distance_Based_Fee_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}
