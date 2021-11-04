<?php
/**
 * Custom Permalink Editor setup.
 *
 * @package KCGCustomPermalinks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Custom Permalink Editor class.
 */
class Custom_Permalink_Editor {
	/**
	 * Custom Permalink Editor version.
	 *
	 * @var string
	 */
	public $version = '1.0.3';

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Define Custom Permalink Editor Constants.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function define_constants() {
		$this->define( 'CP_EDITOR_BASENAME', plugin_basename( CP_EDITOR_FILE ) );
		$this->define( 'CP_EDITOR_PATH', plugin_dir_path( CP_EDITOR_FILE ) );
		$this->define( 'CP_EDITOR_VERSION', $this->version );
	}

	/**
	 * Define constant if not set already.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function includes() {
		include_once CP_EDITOR_PATH . 'includes/class-custom-permalink-editor-form.php';
		include_once CP_EDITOR_PATH . 'includes/class-custom-permalink-editor-frontend.php';
		include_once CP_EDITOR_PATH . 'admin/class-custom-permalink-editor-admin.php';

		$wp_pl_form = new Custom_Permalink_Editor_Form();
		$wp_pl_form->init();

		$cp_frontend = new Custom_Permalink_Editor_Frontend();
		$cp_frontend->init();
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private function init_hooks() {
		register_activation_hook(
			CP_EDITOR_FILE,
			array( 'Custom_Permalink_Editor', 'add_roles' )
		);
		add_action( 'plugins_loaded', array( $this, 'check_loaded_plugins' ) );
		//add_action( 'init', array($this,'check_loaded_plugins') );
	}

	
	public static function add_roles() {
		$admin_role      = get_role( 'administrator' );
		$cp_role         = get_role( 'cp_editor_mr' );
		$current_version = get_option( 'cp_editor_plugin_version', -1 );

		if ( ! empty( $admin_role ) ) {
			$admin_role->add_cap( 'cp_editor_view_post_permalinks' );
			$admin_role->add_cap( 'cp_editor_view_category_permalinks' );
		}

		if ( empty( $cp_role ) ) {
			add_role(
				'cp_editor_mr',
				__( 'Custom Permalink Editor' ),
				array(
					'cp_editor_view_post_permalinks'     => true,
					'cp_editor_view_category_permalinks' => true,
				)
			);
		}
	}

	public function check_loaded_plugins() {
		if ( is_admin() ) {
			$current_version = get_option( 'cp_editor_plugin_version', -1 );

			if ( -1 === $current_version
				|| $current_version < CP_EDITOR_VERSION
			) {
				
				self::add_roles();
			}
		}

		load_plugin_textdomain(
			'custom-permalink-editor',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}
}

new Custom_Permalink_Editor();
