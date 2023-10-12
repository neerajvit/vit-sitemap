<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

if( !class_exists('sitemap_Main') ){

	/**
	 * Plugin Main Class
	 */
	class sitemap_Main
	{
		public $plugin_file;
		public $plugin_dir;
		public $plugin_path;
		public $plugin_url;
	
		/**
		 * Static Singleton Holder
		 * @var self
		 */
		protected static $instance;
		
		/**
		 * Get (and instantiate, if necessary) the instance of the class
		 *
		 * @return self
		 */
		public static function instance() {
			if ( ! self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
		
		public function __construct()
		{
			$this->plugin_file = SITEMAP_PLUGIN_FILE;
			$this->plugin_path = trailingslashit( dirname( $this->plugin_file ) );
			$this->plugin_dir  = trailingslashit( basename( $this->plugin_path ) );
			$this->plugin_url  = str_replace( basename( $this->plugin_file ), '', plugins_url( basename( $this->plugin_file ), $this->plugin_file ) );

			add_action('plugins_loaded', array( $this, 'plugins_loaded' ), 1);
			add_action('admin_menu', array($this,'fn_sitemap_admin_menu_callback'));
			add_action('admin_enqueue_scripts', array($this, 'fn_sitemap_enqueue_admin_scripts'));
			add_action('wp_enqueue_scripts', array($this, 'fn_sitemap_enqueue_front_scripts'));
			add_action('init', array($this, 'fn_sitemap_add_shortcode'));
			add_action( 'save_post', array($this, 'save_sitemap_xml') );
		}
		
		/**
		 * plugin activation callback
		 * @see register_deactivation_hook()
		 *
		 * @param bool $network_deactivating
		 */
		public static function activate() {

		}

		/**
		 * plugin deactivation callback
		 * @see register_deactivation_hook()
		 *
		 * @param bool $network_deactivating
		 */
		public static function deactivate( $network_deactivating ) {

		}
		
		/**
		 * plugin deactivation callback
		 * @see register_uninstall_hook()
		 *
		 * @param bool $network_uninstalling
		 */
		public static function uninstall() {
		   
		}
		
		public function plugins_loaded() {
			$this->loadLibraries();
		}

		/**
		 * Load all the required library files.
		 */
		protected function loadLibraries() {
    		register_setting( 'sitemap-setting-group', 'new_tab_opening' );
    		register_setting( 'sitemap-setting-group', 'exclude_ids' );
		}

		public function fn_sitemap_admin_menu_callback(){

			add_menu_page(__( 'VIT Sitemap', 'vit-sitemap' ),'VIT Sitemap','manage_options','vit-sitemap',array($this,'fn_sitemap_admin_menu_page_callback'),'dashicons-text-page',25);
		}

		function fn_sitemap_admin_menu_page_callback(){

			require_once( $this->plugin_path. 'includes/view/admin/sitemap-options.php');
		}
        
        public function fn_sitemap_enqueue_admin_scripts(){

			wp_enqueue_style( 'sitemap-admin-css', $this->plugin_url."assets/css/admin.css" );
			wp_enqueue_script( 'sitemap-admin-script', $this->plugin_url."assets/js/admin.js" );
			$params = array('ajaxurl' => admin_url( 'admin-ajax.php'),);
			wp_localize_script( 'sitemap-admin-script', 'script_params', $params );
		}

		public function fn_sitemap_enqueue_front_scripts(){

			wp_enqueue_style( 'sitemap-front-css', $this->plugin_url."assets/css/front_style.css" );
		}

		public function fn_sitemap_add_shortcode() {
		
		    add_shortcode('vit_sitemap_generator', array($this, 'fn_sitemap_generator_shortcode'));
		}

		public function fn_sitemap_generator_shortcode() {
						
			ob_start();
			require_once( $this->plugin_path. 'includes/view/shortcodes/html_sitemap.php');
			return ob_get_clean();
		}

		public function generate_sitemap_xml() {

		    $post_types = array('post', 'page');

		    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
		    $sitemap .= '<urlset xmlns:xhtml="http://www.sitemaps.org/schemas/sitemap/0.9">';

		    foreach ($post_types as $post_type) {
		        $args = array(
		            'post_type'      => $post_type,
		            'post_status'    => 'publish',
		            'posts_per_page' => -1,
		        );

		        $query = new WP_Query($args);
		        if ($query->have_posts()) {
		            while ($query->have_posts()) {
		                $query->the_post();
		                $post_title = get_the_title();
		                $post_url = get_permalink();
		                $post_date = get_the_date('Y-m-d\TH:i:s\Z');
		                $sitemap .= '<url>';
		                $sitemap .= '<loc>' . $post_title . '</loc>';
		                // Add the href attribute to the URL
		                $sitemap .= '<xhtml:link rel="alternate" href="' . $post_url . '" />';
		                $sitemap .= '<lastmod>' . $post_date . '</lastmod>';
		                $sitemap .= '</url>';
		            }
		        }
		        wp_reset_postdata();
		    }

		    $sitemap .= '</urlset>';
		    return $sitemap;

		}

		public function save_sitemap_xml() {
		    $sitemap_content = $this->generate_sitemap_xml();

		    // Specify the path and filename for the sitemap XML file
		    $sitemap_file = ABSPATH . 'sitemap.xml';

		    // Save the content to the file
		    $result = file_put_contents($sitemap_file, $sitemap_content);
		}

		public function add_target_blank( $output, $args ) {
		    $output = str_replace( '<a', '<a target="_blank"', $output );
		    return $output;
		}
	}
}

class Custom_Walker_Page extends Walker_Page {
    function start_el(&$output, $page, $depth = 0, $args = array(), $current_page = 0) {
        if ( $depth ) {
            $indent = str_repeat( "\t", $depth );
        } else {
            $indent = '';
        }

        if(!empty(get_option( 'new_tab_opening' ))){
	        $new_tab_opening = '_blank';
	    }else{
	        $new_tab_opening = '_self';
	    }

        $css_class = array( 'page_item', 'page-item-'.$page->ID );

        if ( isset( $args['pages_with_children'][ $page->ID ] ) ) {
            $css_class[] = 'page_item_has_children';
        }

        if ( ! empty( $current_page ) ) {
            $_current_page = get_post( $current_page );

            if ( in_array( $page->ID, $_current_page->ancestors ) ) {
                $css_class[] = 'current_page_ancestor';
            }

            if ( $page->ID == $current_page ) {
                $css_class[] = 'current_page_item';
            } elseif ( $_current_page && $page->ID == $_current_page->post_parent ) {
                $css_class[] = 'current_page_parent';
            }
        } elseif ( $page->ID == get_option('page_for_posts') ) {
            $css_class[] = 'current_page_parent';
        }

        $css_class = implode( ' ', apply_filters( 'page_css_class', $css_class, $page, $depth, $args, $current_page ) );

        $output .= $indent . '<li class="' . $css_class . '">';
        $output .= '<a href="' . get_permalink( $page->ID ) . '" target="' . $new_tab_opening  . '">' . $page->post_title . '</a>';
    }
}