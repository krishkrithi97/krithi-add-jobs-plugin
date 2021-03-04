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

 function add_custom_meta_box()
 {
     add_meta_box("demo-meta-box", "Custom Meta Box", "custom_meta_box_markup", "jobs", "side", "high", null);
 }

 add_action("add_meta_boxes", "add_custom_meta_box");

 function custom_meta_box_markup($object)
{
    wp_nonce_field(basename(__FILE__), "meta-box-nonce");

       // get previously saved meta values (if any)
       $event_start_date = get_post_meta( $object->ID, 'event-start-date', true );
       $event_end_date = get_post_meta( $object->ID, 'event-end-date', true );
       $event_venue = get_post_meta( $object->ID, 'event-venue', true );

       // if there is previously saved value then retrieve it, else set it to the current time
       $event_start_date = ! empty( $event_start_date ) ? $event_start_date : time();

       //we assume that if the end date is not present, event ends on the same day
       $event_end_date = ! empty( $event_end_date ) ? $event_end_date : $event_start_date;


    ?>
        <div>
            <label for="meta-box-text">Title</label>
            <input name="meta-box-text" type="text" value="<?php echo get_post_meta($object->ID, "meta-box-text", true); ?>">

            <br>

            <label for="uep-event-start-date"><?php _e( 'Event Start Date:', 'demo-meta-box' ); ?></label>
                    <input class="widefat uep-event-date-input" id="uep-event-start-date" type="text" name="uep-event-start-date" placeholder="Format: February 18, 2014" value="<?php echo date( 'F d, Y', $event_start_date ); ?>" />

            <label for="uep-event-end-date"><?php _e( 'Event End Date:', 'demo-meta-box' ); ?></label>
                    <input class="widefat uep-event-date-input" id="uep-event-end-date" type="text" name="uep-event-end-date" placeholder="Format: February 18, 2014" value="<?php echo date( 'F d, Y', $event_end_date ); ?>" />

            <br><br>
            <label for="meta-box-text">Email</label>
            <input name="meta-box-text" type="email" value="<?php echo get_post_meta($object->ID, "meta-box-email", true); ?>">



        </div>
    <?php
}

function save_custom_meta_box($post_id, $post, $update)
{
    if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
        return $post_id;

    if(!current_user_can("edit_post", $post_id))
        return $post_id;

    if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;

    $slug = "post";
    if($slug != $post->post_type)
        return $post_id;

    $meta_box_text_value = "";
    $meta_box_start_date_value = "";
    $meta_box_end_date_value = "";
    $meta_box_email_value = "";


    if(isset($_POST["meta-box-text"]))
    {
        $meta_box_text_value = $_POST["meta-box-text"];
    }
    update_post_meta($post_id, "meta-box-text", $meta_box_text_value);


    if(isset($_POST["uep-event-start-date"]))
    {
        $meta_box_start_date_value = $_POST["uep-event-start-date"];
    }
    update_post_meta($post_id, "uep-event-start-date", $meta_box_start_date_value);


    if(isset($_POST["uep-event-end-date"]))
    {
        $meta_box_end_date_value = $_POST["uep-event-end-date"];
    }
    update_post_meta($post_id, "uep-event-end-date", $meta_box_end_date_value);


    if(isset($_POST["meta-box-email"]))
    {
        $meta_box_email_value = $_POST["meta-box-email"];
    }
    update_post_meta($post_id, "meta-box-email", $meta_box_email_value);

}

add_action("save_post", "save_custom_meta_box", 10, 3);




if( !class_exists( 'CutomJobsPlugin' ) ) {

  class CutomJobsPlugin
  {

    function __construct() {
      add_action( 'init', array( $this,'custom_post_type') );
    }

    function register() {
      add_action( 'admin_enqueue_scripts', array( $this,'enqueue') );
    }

    function activate() {
      $this->custom_post_type();
      flush_rewrite_rules();
    }

    function deactivate() {
      flush_rewrite_rules();
    }

    function custom_post_type() {
      register_post_type( 'jobs',['public' => true, 'label' => 'Custom-Jobs',
      'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments')] );
    }

    function  enqueue() {
      wp_enqueue_style( 'mypluginstyle', plugins_url( '/assets/mystyle.css', __FILE__) );
      wp_enqueue_script( 'mypluginscript', plugins_url( '/assets/myscript.js', __FILE__) );
    }
  }

  if ( class_exists( 'CutomJobsPlugin' ) ) {
    $cutomJobsPlugin = new CutomJobsPlugin();
    $cutomJobsPlugin->register();
  }

//plugin_activation

  register_activation_hook(__FILE__ , array( $cutomJobsPlugin, 'activate' ) );

//plugin_deactivation

  register_deactivation_hook(__FILE__ , array( $cutomJobsPlugin, 'deactivate' ) );

}
//uninstall
