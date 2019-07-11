<?php
 /**
  * @author Scott Johnston
  * @license https://www.gnu.org/licenses/gpl-3.0.html
  * @package Astraea
  * @version 1.0.0
 */

defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );

class AstraeaAdmin{		

    private $option_group =  'astraea-config-group';

	public function __construct(){
        add_action( 'admin_menu', array($this,'add_menu') );
        add_action( 'admin_init', array($this, 'register_configure_parameters') );
    }
    
    function register_configure_parameters() {         
        register_setting( $this->option_group, 'icon_size', array('string', 'comment icon width',null ,false , '96rem') );  
        register_setting( $this->option_group, 'textarea_length', array('string', 'comment textarea length',null ,false , 100) );
        register_setting( $this->option_group, 'textarea_rows', array('integer', 'comment textarea rows',null ,false , 3) );  
        register_setting( $this->option_group, 'review_field_name', array('string', 'review field name',null ,false , 'custom_field_review') );  
        
    }    

	function add_menu() {
        $menu_title = 'astraea-info-page';
        $capability = 'manage_options';
		add_menu_page( 'Info', 'Astraea', $capability, $menu_title, array($this, 'add_info_page'), 'dashicons-star-filled', 4 );        
        add_submenu_page( $menu_title, 'Astraea Look and Feel', 'Configuration', $capability, 'astraea-configuration-page' , array($this, 'add_configuration_page') );	        				
	}

	public function add_info_page(){
        $plugin_data = get_plugin_data( plugin_dir_path(__FILE__).'astraea.php') ;
        echo "<h1>".$plugin_data["Name"]." Info</h1>";       
		echo "<p>".$plugin_data["Description"]."</p>";        
        ?>
        <h2>Checklist</h2>
        <ol>
            <li>By default this plugin uses the user meta field custom_field_review to store the users average rating.</li>             
        </ol>       
        <h2>Examples</h2>
        <ul>
            <li><code>[astraea_average to='1']</code></li>  
            <li><code>[astraea_average average='60']</code></li>  
            <li><code>[astraea_create to='1']</code></li>  
            <li><code>[astraea_list to='1' write='false']</code></li>  
        </ul>        
        <h2>Plugin</h2>
        <ul>        
            <li>Version:<?php echo $plugin_data["Version"];  ?></li> 
            <li>URL: <a href='<?php echo $plugin_data["PluginURI"];  ?>'><?php echo $plugin_data["Name"] ?></a></li>
        </ul>
        <?php 
       
	}
    
    public function add_configuration_page(){	
        ?>

        <h1>Astraea Configure</h1>
            <form method='post' action='options.php'>	
            <?php settings_fields( $this->option_group ); ?>
            <?php do_settings_sections( $this->option_group ); ?>	
                <h2>Look and feel</h2>
                Textarea Length<br>
                <input  name="config_option_textarea_length" type='number' value="<?php 
                echo (!empty(get_option('config_option_textarea_length'))) ? filter_var ( get_option('config_option_textarea_length') , FILTER_SANITIZE_NUMBER_INTL ) :  100; 
                ?>" placeholder = "textarea width"/><br>

                Textarea rows<br>
                <input  name="config_option_textarea_rows" type='number' value="<?php 
                    echo (!empty(get_option('config_option_textarea_rows'))) ? get_option('config_option_textarea_rows') : "3"; 
                ?>" placeholder = "textarea rows"/><br>	
               
                Icon size<br>
                <select name="icon_size" value = <?php (!empty(get_option('icon_size'))) ? get_option('icon_size') : '96rem' ; ?>>
                    <option value="16rem"<?php if(get_option('icon_size') == '16rem'): ?> selected="selected"<?php endif; ?>>16rem</option>
                    <option value="32rem"<?php if(get_option('icon_size') == '32rem'): ?> selected="selected"<?php endif; ?>>32rem</option>
                    <option value="48rem"<?php if(get_option('icon_size') == '48rem'): ?> selected="selected"<?php endif; ?>>48rem</option>
                    <option value="64rem"<?php if(get_option('icon_size') == '64rem'): ?> selected="selected"<?php endif; ?>>64rem</option>
                    <option value="96rem"<?php if(get_option('icon_size') == '96rem'): ?> selected="selected"<?php endif; ?>>96rem</option>
                </select>

                <h2>User custom fields</h2>
                Review<br>
                <input  name="review_field_name" type='text' value="<?php 
                echo (!empty(get_option('review_field_name'))) ? filter_var ( get_option('review_field_name') , FILTER_SANITIZE_EMAIL ) :  "custom_field_review"; 
                ?>" placeholder = "textarea width"/><br>
               

                <?php submit_button(); ?>			
            </form>

         <?php
	}
}

$astraeaAdmin = new AstraeaAdmin;
?>