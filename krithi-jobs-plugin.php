  <?php
  /**
  * @package PoemhdPlugin
  */
  /*
   * Plugin Name:       Jobs Plugin
   * Plugin URI:        https://example.com/plugins/the-basics/
   * Description:       Handle the basics with this plugin.
   * Version:           1.10.3
   * Requires at least: 5.2
   * Requires PHP:      7.2
   * Author:            Krithi Krishna
   * Author URI:        https://author.example.com/
   * License:           GPL v2 or later
   * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
   * Text Domain:       custom-jobs-plugin
   * Domain Path:       /languages
   */
   /*
   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License
   as published by the Free Software Foundation; either version 2
   of the License, or (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

   Copyright 2005-2015 Automattic, Inc.
   */
   defined( 'ABSPATH' ) or die('Hey,you can\t access this file!' );


  if( !class_exists( 'CutomJobsPlugin' ) ) {

    class CutomJobsPlugin
    {

      function __construct() {
        add_action( 'init', array( $this,'custom_post_type') );
        add_action( 'add_meta_boxes', array( $this,'jobs_add_expiry_date_metabox') );
        add_action( 'save_post', array( $this,'jobs_save_expiry_date_meta' ));
        add_action( 'add_meta_boxes', array( $this,'jobs_add_metabox') );
        add_action( 'save_post', array( $this,'save_jobs_meta' ));
        add_action( 'pre_get_posts', array( $this,'jobs_filter_expired_posts' ));
      }

      function register() {
      add_action( 'admin_enqueue_scripts', array( $this,'jobs_load_jquery_datepicker') );
      }

      // function tutsplus_add_expiry_date_metabox() {
      //   add_meta_box( 'expiry-date','Custom Expiry Date',array($this,'tutsplus_expiry_date_metabox_callback'),'jobs','side','core');
      // }
      function jobs_add_expiry_date_metabox() {
          add_meta_box(
              'jobs_expiry_date_metabox',
              __( 'Custom Expiry Date', 'jobexp'),
              array($this,'jobs_expiry_date_metabox_callback'),
              'jobs',
              'side',
              'core'
          );
      }

      function jobs_expiry_date_metabox_callback( $post ) {
      ?>

        <form action="" method="post">

            <?php
            // add nonce for security
            wp_nonce_field( 'jobs_expiry_date_metabox_nonce', 'jobs_nonce' );

            //retrieve metadata value if it exists
            $jobs_expiry_date = get_post_meta( $post->ID, 'expires', true );
            ?>

            <label for "jobs_expiry_date"><?php ('Expiry Date'); ?></label>

            <input type="text" class="MyDate" name="jobs_expiry_date" value=<?php echo esc_attr( $jobs_expiry_date ); ?> >

            <script type="text/javascript">
                jQuery(document).ready(function() {
                    jQuery('.MyDate').datepicker({
                        dateFormat : 'dd-mm-yy'
                    });
                });
            </script>

        </form>

    <?php }
    function jobs_save_expiry_date_meta( $post_id ) {
        // write_log(test);
        // Check if the current user has permission to edit the post. */
        if ( !current_user_can( 'edit_post', $post_id->ID ) )
        return;

        if ( isset( $_POST['jobs_expiry_date'] ) ) {
            $new_expiry_date = ( $_POST['jobs_expiry_date'] );
            update_post_meta( $post_id, 'expires', $new_expiry_date );
        }

    }


    function jobs_filter_expired_posts( $query ) {

      // doesn't affect admin screens
      if ( is_admin() )
          return;
      // check for main query
      if ( $query->is_main_query() ) {

          //filter out expired posts
          $today = date('d-m-Y');
          $metaquery = array(
              array(
                   'key' => 'expires',
                   'value' => $today,
                   'compare' => '<',
                   'type' => 'DATE',
              )
          );
          $query->set( 'meta_query', $metaquery );
      }
    }


    function jobs_add_metabox() {
        add_meta_box(
            'jobs_metabox',
            __( 'Custom Meta Box', 'jobs-meta'),
            array($this,'jobs_metabox_callback'),
            'jobs',
            'side',
            'core',
        );
    }

    function jobs_metabox_callback( $post ) {

        // Add a nonce field so we can check for it later.
        wp_nonce_field( 'global_notice_nonce', 'global_notice_nonce' );

        $value1 = get_post_meta( $post->ID, '_title-meta', true );
        $value2 = get_post_meta( $post->ID, '_email-meta', true );
        ?>
        <form action="" method="POST" enctype="multipart/form-data" name="form">
          <label for="meta-box-title">Title</label>
          <input type="text" name="title-meta" value="<?php echo esc_html( $value1 ); ?>">
          <br>
          <br>

          <label for="meta-box-email">Email</label>
          <input type="email" name="email-meta" value="<?php echo esc_html( $value2 ); ?>">
  <?php
    }




    /**
     * When the post is saved, saves our custom data.
     *
     * @param int $post_id
     */
    function save_jobs_meta( $post_id ) {

        // Check if our nonce is set.
        if ( ! isset( $_POST['global_notice_nonce'] ) ) {
            return;
        }

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $_POST['global_notice_nonce'], 'global_notice_nonce' ) ) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check the user's permissions.
        if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return;
            }

        }
        else {

            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
        }

        /* OK, it's safe for us to save the data now. */

        // Make sure that it is set.
        if ( ! isset( $_POST['title-meta'] ) ) {
            return;
        }

        // Sanitize user input.
        $my_data = sanitize_text_field( $_POST['title-meta'] );

        // Update the meta field in the database.
        update_post_meta( $post_id, '_title-meta', $my_data );

        if ( ! isset( $_POST['email-meta'] ) ) {
            return;
        }

        // Sanitize user input.
        $my_data1 = sanitize_email( $_POST['email-meta'] );

        // Update the meta field in the database.
        update_post_meta( $post_id, '_email-meta', $my_data1 );
    }




      function jobs_load_jquery_datepicker() {
          wp_enqueue_script( 'jquery-ui-datepicker' );
          wp_enqueue_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
      }


      function activate() {
        $this->custom_post_type();
        flush_rewrite_rules();
      }

      function deactivate() {
        flush_rewrite_rules();
      }


      function custom_post_type() {
        register_post_type( 'jobs', ['public' => true, 'label' => 'Custom Jobs',] );
      }


     function  enqueue() {
       wp_enqueue_style( 'mypluginstyle', plugins_url( '/assets/mystyle.css', __FILE__) );
       wp_enqueue_script( 'mypluginscript', plugins_url( '/assets/myscript.js', __FILE__) );
     }
    }

    $cutomJobsPlugin = new CutomJobsPlugin();
    $cutomJobsPlugin->register();



  //plugin_activation

    register_activation_hook(__FILE__ , array( $cutomJobsPlugin, 'activate' ) );

  //plugin_deactivation

    register_deactivation_hook(__FILE__ , array( $cutomJobsPlugin, 'deactivate' ) );

}

    if (!function_exists('write_log')) {
    	function write_log ( $log )  {
    		if ( true === WP_DEBUG ) {
    			if ( is_array( $log ) || is_object( $log ) ) {
    				error_log( print_r( $log, true ) );
    			} else {
    				error_log( $log );
    			}
    		}
    	}
    }



  //uninstall
