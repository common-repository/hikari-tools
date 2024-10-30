<?php

/**
	Hikari Tools is a class I, Hikari from http://wordpress.Hikari.ws, collect many tools, functions and codes I use very frequently among my plugins and themes.
	
	It's meant to be a virtual class, from which I extend my classes and  use its code.

*/






class HkTools{

	public $debug = false;
	protected $startup = true;
	protected $expire = 604800; // 1 week
	
	protected static $isSingleton = false;
	protected static $thisObject = null;

	protected $plugin_dir_path;
	protected $plugin_dir_url;
	public $pversion = 0;
	
	
	public function __construct($pluginFile=__FILE__){

		$this->plugin_dir_path = plugin_dir_path($pluginFile);
		$this->plugin_dir_url = plugin_dir_url($pluginFile);
	
		if($this->startup) add_action('init', array($this, 'startup'));
	}

	// a singleton class must overwrite __construct() and __clone(), setting them to private
	// see http://en.wikipedia.org/wiki/Singleton_pattern#PHP
	public static function getInstance(){
		if(self::$isSingleton){
			if(self::$thisObject===null) self::$thisObject = new self();
			return self::$thisObject;
		}else{
			return new self();
		}
	}
	
	
	public function startup(){}
	



// -------------------------------------
//	String manipulation
// -------------------------------------

	public static function replace_accents($string){
	  return str_replace(
			array('à','á','â','ã','ä', 'ç', 'è','é','ê','ë', 'ì','í','î','ï', 'ñ', 'ò','ó','ô','õ','ö', 'ù','ú','û','ü', 'ý','ÿ',
				'À','Á','Â','Ã','Ä', 'Ç', 'È','É','Ê','Ë', 'Ì','Í','Î','Ï', 'Ñ', 'Ò','Ó','Ô','Õ','Ö', 'Ù','Ú','Û','Ü', 'Ý',
				'?', '!', ' '),
			array('a','a','a','a','a', 'c', 'e','e','e','e', 'i','i','i','i', 'n', 'o','o','o','o','o', 'u','u','u','u', 'y','y',
				'A','A','A','A','A', 'C', 'E','E','E','E', 'I','I','I','I', 'N', 'O','O','O','O','O', 'U','U','U','U', 'Y',
				'', '', '_'),
			$string);

	}


/* 	
isBlank tests to make sure a string is _really_ non-null.  It accepts one argument:
	$string - the string to be tested

It returns true if the string is really a null string, and false otherwise.
*/	
	public static function isBlank($string){
		$string = trim($string);
	
		if(empty($string)) return true;
		
		for($i = 0; $i < strlen($string); $i++){
			$c = substr($string, $i, 1);
			if( ($c != "\r" ) && ($c != " ") && ($c != "\n") && ($c != "\t") && ($c != "\0") && ($c != "\x0B")){
				return false;
			}
		}
		return true;
	}

	// bool to string, to be passed as parameter to JavaScript
	public static function bTOs($bool){
		if($bool)	return "true";
		else		return "false";
	}
	
	public static function explodeString($string, $separator=',' ,$trim=true){
		if(empty($string))	return null;
		else{
			$group = explode($separator,$string);
			
			if($trim)
				foreach($group as $key => $value)
					$group[$key] = trim($value);

			return $group;
		}
	}
	
	public static function randomString($size=10){
		$characters = 'abcdefghijklmnopqrstuvwxyz';
		$result="";
		
		for($p = 0; $p < $size; $p++){
			$result .= $characters[mt_rand(0, strlen($characters))];
		}
		
		return $result;
	}
	
	
	
	
	// deprecated, use dump() instead
	public static function echoArray($array){
		echo "\n\n<pre>\n" . print_r($array,true) . "\n</pre>\n\n";
	}
	
	public static function dump($var,$label=null,$type="krumo"){
	
		switch($type){
			case "print_r":
				echo "\n\n<div class='HkTools'>";
				if(!empty($label)) echo "<p>$label</p>\n";
				echo self::formatCode(print_r($var,true)) . "</div>\n\n";
			
				break;
			case "var_dump":
				echo "\n\n<div class='HkTools'>";
				if(!empty($label)) echo "<p>$label</p>\n";
				
				ob_start();
				var_dump($var);
				$content = ob_get_contents();
				ob_end_clean();
				
				echo self::formatCode($content) . "</div>\n\n";
			
				break;
			
			case "krumo":
			default:
				if(function_exists("krumo")){
					if(krumo::isKrumoEnabled()){
						echo "\n\n<div class='HkTools'>";
						if(!empty($label)) echo "<p>$label</p>\n";
						krumo($var);
						echo "\n</div>\n\n";
					}
				}else{
					self::dump($var,$label,"print_r");
				}
		
		}
	
	}
	
	// send $code without any markup
	public static function formatCode($code){
		if(empty($code)) $code = ' ';
	
		if(class_exists("SyntaxHighlighter")){
			global $SyntaxHighlighter;
			$SyntaxHighlighter->codeformat = 1;
			$formated = $SyntaxHighlighter->parse_shortcodes( "[php]{$code}[/php]" );
			$SyntaxHighlighter->codeformat = false;
			return $formated;

/*
// CodeColorer support still bugged
		}elseif(class_exists('CodeColorer')){
			$codeColorer = &CodeColorer::GetInstance();
			return $codeColorer->GetCodeHighlighted($code);
*/
		}else{
			return "<pre>\n{$code}\n</pre>";
		}
	
	
	
	}
	

	public static function urlencode_array($args){
		if(!is_array($args)) return $args;
		$c = 0;
		$out = '';
		
		foreach($args as $name => $value){
			if($c++) $out .= '&';	// htmlentities()
			$out .= urlencode("$name=");
			if(is_array($value)){
				$out .= urlencode(serialize($value));
			}else{
				$out .= urlencode("$value");
			}
		}
		return $out;
	}
	
// -------------------------------------
//	Cache
// -------------------------------------

	public function getArrayCache($index, $key, $group="default"){
		$cache = wp_cache_get($key, $group);
		
		if(false===$cache)				return null;
		elseif(!is_array($cache))		return null;
		elseif($cache["ht"] < time()){
			wp_cache_delete($key, $group);
			return null;
		}elseif(!isset($cache["hd"][$index]))		return null;
		elseif($cache["hd"][$index]["t"] < time())	return null;
		else							return $cache["hd"][$index]["d"];
	}
	
	public function setArrayCache($index, $key, $data, $group="default", $expire=0){
		$cache = wp_cache_get($key, $group);
		if($expire<10) $expire=$this->expire;
	
		if(false===$cache || !is_array($cache) || $cache["ht"]<time()){
			$cache = array();
			$cache["ht"] = time() + $expire;
		}
		
		$cache["hd"][$index]["t"] = time() + $expire;
		$cache["hd"][$index]["d"] = $data;
		
		wp_cache_set($key, $cache, $group, $expire);
	}
	
	public function deleteArrayCache($index, $key, $group = 'default'){
		$cache = wp_cache_get($key, $group);
		
		if(false===$cache)				return false;
		elseif(!is_array($cache))		return false;
		elseif($cache["ht"] < time()){
			wp_cache_delete($key, $group);
			return false;
		}

		// there would be still test for isset and time expiration, that would return false too, but for performance reasons it's skipped
		unset($cache["hd"][$index]);
		wp_cache_set($key, $cache, $group, $this->expire);
		return true;
	}


// -------------------------------------
//	Misc
// -------------------------------------
	
	// from Hackadelic SEO Table Of Contents plugin, http://hackadelic.com/solutions/wordpress/seo-table-of-contents
	public static function assignTo(&$var, $value) {
		settype($value, gettype($var));
		$var = $value;
	}
	public static function setVar(&$var, $mainVal, $auxVal) {
		$var = $mainVal ? $mainVal : $auxVal;
	}


	// just a more abstracted replacement for strpos
	// returns true if $needle was found, false otherwhise
	public static function hasNeedle($haystack,$needle){
		foreach((array) $needle as $aNeedle){
			if(strpos($haystack,$aNeedle) !== false) return true;
		}
		return false;
	}
	public static function hasNeedleInsensitive($haystack,$needle){
		foreach((array) $needle as $aNeedle){
			if(stripos($haystack,$aNeedle) !== false) return true;
		}
		return false;
	}









}



class HkToolsOptions extends HkTools{




// -------------------------------------
//	Admin options page
// -------------------------------------


/*
//Exemple of an options structure:

$op_structure = array(

	array(
		"title" => "General Settings",
		"help" => "Basic settings.",
		"options" => array(
	


			"text_test" => array(	"name" => "Text Test",
					"desc" => "This option is a test of a  possible option whose value is of type 'text'",
					"largeDesc" => "<p class=\"description\">This option is a test of a  possible option whose value is of type 'text'</p>",
					"id" => "text_test",
					"default" => 'text default value',
					"type" => "text",
					"options" => array("size" => "65", "full_width" => true)
			),
			
			'textarea_test' => array(	"name" => 'TextArea Test',
					"desc" => "This option is a test of a  possible option whose value is of type 'textarea'",
					"largeDesc" => "<p class=\"description\">This option is a test of a  possible option whose value is of type 'textarea'</p>",
					"id" => 'textarea_test',
					"default" => "Powered by [wp-link]. Built on the [theme-link].",
					"type" => "textarea",
					"options" => array(	"rows" => "5",
								"cols" => "100",
								"full_width" => true,
								"stripslashes" => true)
			),
			
			'select_test' => array(	"name" => 'Select Test',
					"desc" => "This option is a test of a  possible option whose value is of type 'select'",
					"largeDesc" => "<p class=\"description\">This option is a test of a  possible option whose value is of type 'select'</p>",
					"id" => 'select_test',
					"default" => 'key4',
					"type" => "select",
					"options" => array(
							'key1'	=> "option1",
							'key2'	=> "option2",
							'key3'	=> "option3",
							'key4'	=> "option4",
							'key5'	=> "option5"
					)
			),
			
			'radiobox_test' => array(	"name" => 'Radiobox Test',
					"desc" => "This option is a test of a  possible option whose value is of type 'radio'",
					"largeDesc" => "<p class=\"description\">This option is a test of a  possible option whose value is of type 'radio'</p>",
					"id" => 'radiobox_test',
					"default" => 'key4',
					"type" => "radio",
					"options" => array(
							'key1'	=> "option1",
							'key2'	=> "option2",
							'key3'	=> "option3",
							'key4'	=> "option4",
							'key5'	=> "option5"
					)
			),
			
			"checkbox_test" => array( "name" => "Checkbox Test",
					"desc" => "This option is a test of a  possible option whose value is of type 'checkbox'",
					"largeDesc" => "<p class=\"description\">This option is a test of a  possible option whose value is of type 'checkbox'</p>",
					"id" => "checkbox_test",
					"default" => array('op1' => true, 'op2' => false, 'op3' => false, 'op4' => true ),	// even items with default value as false must be included
					"type" => "checkbox",
					"options" => array(
								array(
									'check_id'	=> 'op1',
									'desc'		=> 'op1 description'
								),
								array(
									'check_id'	=> 'op2',
									'desc'		=> 'op2 description'
								),
								array(
									'check_id'	=> 'op3',
									'desc'		=> 'op3 description'
								),
								array(
									'check_id'	=> 'op4',
									'desc'		=> 'op4 description'
								)
					)
			),
			
			'custom_test' => array(	"name" => 'Custom option Test',
					"desc" => "This option is a test of a  possible option whose tyle is 'custom'",
					"largeDesc" => "<p class=\"description\">'custom' means the class consumer can define a callback, which will print custom HTML code</p>",
					"id" => 'custom_test',
					"default" => null,	// any value can be used in default, just make sure it's a valid value related to the data type your custom code will use
					"type" => "custom",
					"options" => array(	"callback" => "callback_function",
								"parameter" => array("name1"=>"value1", "name2"=>"value2")
								)
			)
			
		)
	),
	
	array(
		"title" => "Test Settings",
		"help" => null,
		"options" => array(

			"text2" => array(	"name" => "Text Test",
					"desc" => "This option is a test of a  possible option whose value is of type 'text'",
					"largeDesc" => "<p class=\"description\">This option is a test of a  possible option whose value is of type 'text'</p>",
					"id" => "text2",
					"default" => null,
					"type" => "text",
					"options" => array("size" => "65", "full_width" => true)
			)
		)
	)
	
);
			
*/

	//protected static $isSingleton = true;

	public $optionsName;
	protected $pluginfile;
	protected $devFeed = 'http://feeds.feedburner.com/HikariWebsiteDev';
	
	
	public $optionspageName;
	protected $pluginURL;
	public $optionspagePath;
	
	protected $optionsDBName;
	protected $optionsDBGroup;
	public $optionsDBValue;
	
	protected $optionsDBVersion = 0;
	
	
	protected $opStructure;
	protected $uninstallArgs;
	
	
	
	
//	protected function __clone(){ return parent::__clone(); }
	public function __construct($pluginFile=__FILE__){
		require_once ABSPATH.'/wp-admin/includes/plugin.php';
		$pluginInfo = get_plugin_data($this->pluginfile);
	
		$this->optionspageName = $pluginInfo['Name'];
		$this->pluginURL = $pluginInfo['PluginURI'];
	
		$this->optionspagePath = 'options-general.php?page='.$this->optionsName;
		$this->optionsDBName = $this->optionsName.'_options';
		$this->optionsDBGroup = $this->optionsDBName.'_group';
		
		$this->loadAllOptions();
		


		if(is_admin()){
			add_action('admin_init', array($this,'options_init'));
			add_action('admin_menu', array($this,'menuPrepare'));
		}
		
		parent::__construct($pluginFile);
	}
	
	
	
	
	public function loadAllOptions(){
		$this->optionsDBValue = $this->loadOptions($this->optionsDBName, $this->opStructure);
	}
	
	public function loadOptions($dbName, $opStrucutre){
		
		$dbValue = get_option($dbName);
		
		if(empty($opStrucutre)) return $dbValue;
		
		// this option isn't saved on database, use default
		if( empty($dbValue) ){
			$dbValue['dbVersion'] = $this->optionsDBVersion;
		
			foreach($opStrucutre as $section) foreach($section["options"] as $opItem){
				$dbValue[ $opItem['id'] ] = $opItem['default'];
			}
		}
		
		// if it's saved, and version is different from expected, call function to deal with it
		elseif($dbValue['dbVersion'] != $this->optionsDBVersion)
				$dbValue = $this->options_version_verify($dbName,$dbValue);
		
		
		return $dbValue;
	}
	
	
	public function options_init(){
		register_setting( $this->optionsDBGroup, $this->optionsDBName );
	}
	

	public function menuPrepare(){
		$plugin_page = add_options_page($this->optionspageName.' Options', $this->optionspageName,
				'manage_options', $this->optionsName, array($this,'options_page'));
		
		add_action( 'admin_head-'.$plugin_page, array($this,'optionsHead') );
		
		// if reset parameter is set, then update parameter for sure isn't, it's just remaining from old get parameters,
		//that the browser didn't delete when submited new post form
		if(isset($_REQUEST['reset'])) unset($_GET['updated']);
		
		return $plugin_page;
	}
	
	public function optionsHead(){?>
<style type="text/css">
.hikari-content{ width: 80%; }
.hikari-content pre{ overflow: auto; max-height: 400px; }
.hikari-sidebar{ float:right; padding: 0 10px 10px;; min-width: 170px; width: 18%; min-height: 400px; margin-bottom: 3em; position: static; }
.hikari-sidebar .widget{ background:#FFF; border:solid 1px #DDD; margin-bottom:10px; }
.hikari-sidebar .title{ background: #DBDBB6; border: #E1D6C6 1px solid; color: #6666FF; font-weight: bold; font-size: 1.2em; padding: .4em; text-align: center; }
.hikari-sidebar ul{ margin:10px 5px 5px; }
.hikari-sidebar li{ margin:0 0 9px 20px; list-style: disc; }
.hikari-sidebar .donate{ background:#c9defa; padding:10px; text-align: center; }

.form-table{ clear: none; }
.hikari-uninstall-form{ margin-top: 5em; }
.hikari-uninstall-table{ clear: none; }
.hikari-postbox{ position: static; }
.hikari-form{ margin-bottom: 7em; }



dl { padding: 0; margin: 10px 1em 20px 0; }
dt { font-size: 1.3em; font-weight: bold; margin:0; padding: 4px 10px 4px 10px; border: 1px solid #ddd;
	background: #dfdfdf url(<?php echo admin_url('images/gray-grad.png') ?>) repeat-x left top;
<?php foreach (array('-moz-', '-opera-', '-khtml-', '-webkit-', '') as $pfx)
			echo " {$pfx}border-radius: 6px 6px 0 0;" ?>
	}
dt a, dt img { cursor:pointer;cursor:hand; }
dd { margin: 0 0 2em; padding: 10px 20px 10px 20px; border: 1px solid #ddd; background-color: white;
<?php foreach (array('-moz-', '-opera-', '-khtml-', '-webkit-', '') as $pfx)
			echo " {$pfx}border-radius: 0 0 6px 6px;" ?>
}

dd .caveat { font-weight: bold; color: #C00; text-align: center }

.box { border: 1px solid #ccc; padding: 5px; margin: 5px }
.help { background-color: whitesmoke }
</style>
<?php	}
	


	






/*
	code based on Thematic theme and Hackadelic SEO Table Of Contents
*/
	public static function print_admin_options($op_structure,$database_name,$database_values){
	
		if(empty($op_structure)) return;
?>
<dl>
<?php

		$snr = 0;
		foreach($op_structure as $op_section){
			$snr += 1; $shlpid = "shlp-$snr";
?>
	<dt><?php echo $op_section["title"];
	if($op_section["help"]){
		?> <a onclick="jQuery('#<?php echo $shlpid; ?>').slideToggle('fast')"><img src="<?php echo plugin_dir_url(__FILE__); ?>help.png" /></a><?php
	} ?></dt>
	<dd>
<?php
			if($op_section["help"]){ ?>
	<div id="<?php echo $shlpid; ?>" class="hidden help box"><?php echo $op_section["help"]; ?></div>
<?php
			}
?>
		<table class="form-table"><tbody>
<?php
			foreach($op_section["options"] as $option){
				self::print_admin_option($option,$database_name,$database_values);
			}
?>
		</tbody></table>
	</dd>
<?php	} ?>
</dl>
<?php


	}



























	public static function print_admin_option($options_item, $database_name, $database_values){


		
	
		$nameid = 'name="'.$database_name.'['.$options_item['id'].']" id="'.$database_name.'['.$options_item['id'].']"';
		
		$th_with_for = '<th scope="row">' .
					'<label class="row-title" for="'.$database_name.'[' . $options_item['id'] . ']">' . $options_item['name'] . '</label>';
			if(!empty($options_item['desc'])) $th_with_for .= '<p class="description">'.$options_item['desc'].'</p>';
		$th_with_for .= '</th>';
		
		$th_without_for = '<th scope="row">' .
					'<label class="row-title">' . $options_item['name'] . '</label>';
			if(!empty($options_item['desc'])) $th_without_for .= '<p class="description">'.$options_item['desc'].'</p>';
		$th_without_for .= '</th>';
		
		
		$largeDesc = $options_item['largeDesc'];

		
		
		
		switch( $options_item['type'] ){
			case 'text':
			$tx_width = $options_item['options']['full_width'] ? ' class="widefat"' : '';
			
			$tx_value = $database_values[ $options_item['id'] ];
			if(is_array($tx_value)) $tx_value = implode(',',$tx_value);
			?>
			<tr> 
				<?php echo $th_with_for; ?>
				<td>
					<input type="<?php echo $options_item['type']; ?>" size="<?php echo $options_item['options']['size']; ?>" <?php echo $nameid; echo $tx_width; ?> value="<?php echo htmlspecialchars($tx_value); ?>" />

					<?php echo $largeDesc; ?>
				</td>
			</tr>
			<?php
			break;
			
			case 'textarea':
			$ta_options = $options_item['options'];
			$ta_width = $ta_options['full_width'] ? ' class="widefat"' : '';
			
			$ta_value = $database_values[ $options_item['id'] ];
			if(is_array($ta_value)) $ta_value = implode("\n",$ta_value);
			if($ta_options['stripslashes']){
				$ta_value = stripslashes($ta_value);
			}
			?>
			<tr> 
				<?php echo $th_with_for; ?>
				<td>
					<textarea <?php echo $nameid; echo $ta_width; ?> cols="<?php echo $ta_options['cols']; ?>" rows="<?php echo $ta_options['rows']; ?>"><?php echo htmlspecialchars($ta_value); ?></textarea>
				
					<?php echo $largeDesc; ?>
				</td>
			</tr>
			<?php
			break;

			case 'select':
			?>
			<tr>
				<?php echo $th_with_for; ?>
				<td>
					<select <?php echo $nameid; ?>>
						<?php
						$select_setting = $database_values[ $options_item['id'] ];
					
						foreach($options_item['options'] as $key=>$option) {
						?>
						<option value="<?php echo $key; ?>" <?php selected($select_setting,$key); ?>><?php echo $option; ?></option>
						<?php } // foreach  ?>
					
					</select>

					<?php echo $largeDesc; ?>
				</td>
			</tr>
			<?php
			break;
			
			case 'radio':
			?>
			<tr> 
				<?php echo $th_without_for; ?>
				<td><?php
					
					// this goes on the 'name' attribute, because it's equal for all radios and is the path to create the database array
					$database_entry = $database_name.'['.$options_item['id'].']';
					
					$radio_setting = $database_values[ $options_item['id'] ];
					
					foreach($options_item['options'] as $key=>$option_label) {
					
						// this is the radios' IDs, it's unique on the HTML document and must be used on its label 'for' attribute
						// the idea to make it unique is use the database 'option name',  this $options_item['id'], and this radio key
						$radio_id = $database_name.'_'.$options_item['id'].'_'.$key;

						?>
						<input type="radio" name="<?php echo $database_entry; ?>" id="<?php echo $radio_id; ?>" value="<?php echo $key; ?>" <?php checked($radio_setting,$key); ?> />
						<label for="<?php echo $radio_id; ?>"><?php echo $option_label; ?></label>
						<br />
					<?php } ?>
					
					<?php echo $largeDesc; ?>
				</td>
			</tr>
			<?php
			break;
			
			case 'checkbox':
			?>
			<tr> 
				<?php echo $th_without_for; ?>
				<td><?php
				
					$checkbox_group = $database_values[ $options_item['id'] ][ 'group' ];
					$checkbox_group_namid = $database_name.'['.$options_item['id'].'][group]';
					
				
					foreach($options_item['options'] as $check_item){
				
						$check_nameid = $database_name.'['.$options_item['id'].']['.$check_item['check_id'].']';
						
						$check_setting = $database_values[ $options_item['id'] ][ $check_item['check_id'] ];

						if ($check_setting) {
							$checked = 'checked="checked"';
						} else {
							$checked = "";
						}

						?>
						<input type="checkbox" name="<?php echo $check_nameid; ?>" id="<?php echo $check_nameid; ?>" value="on" <?php echo $checked; ?> />
						<label for="<?php echo $check_nameid; ?>"><?php echo $check_item['desc']; ?></label>
						<br />
					<?php } ?>
					
					<input type="hidden" name="<?php echo $checkbox_group_namid; ?>" id="<?php echo $checkbox_group_namid; ?>" value="on" />
					
					<?php echo $largeDesc; ?>
				</td>
			</tr>
			<?php
			break;
			
			case 'custom':
			
				if(is_callable($options_item['options']['callback'])) {

					call_user_func(
							$options_item['options']['callback'],
							$options_item['options']['parameter']
						);
				
				}else{
				?>
			<tr><td>
				<p>This option is of type "custom", which means it should call a custom function/object method defuned by plugin author, but its callback is invalid, it's not callable. Here's the option raw data:</p>
				<p>&nbsp;</p>
				<?php
				$this->dump($options_item);
				?>
			</td></tr>
				<?php
				}
			
			


				
			
			break;
			
			
			
			default:

			break;
		}

	
	
	
	
	
	}
	
	
/*
	$uninstallArgs strucuture:
	
	- $uninstallArgs['name']: the name of the plugin
	- $uninstallArgs['plugin_basename']: plugin's folder and main file's name, should be empty if it's a theme and not a plugin
	- $uninstallArgs['options'] : an array listing different database storages, it can be wp_options, postmeta, commentmeta, table
		. $uninstallArgs['options']['opType']: the type of this option, it can be 'wp_options', 'postmeta', 'commentmeta', 'table'
		. $uninstallArgs['options']['itemNames']: an array with each wp_options / postmeta / commentmeta / table  name that will be deleted
*/
	
	// code based on WP-Views plugin
	public function uninstallForm($uninstallArgs){
		//echo '<pre>'; print_r($uninstallArgs); echo '</pre>';
?>
<form class="hikari-uninstall-form" method="post" action="">
<div class="wrap"> 
	<h3>Uninstall <?php echo $uninstallArgs['name']; ?></h3>
	<p>Deactivating <strong><?php echo $uninstallArgs['name']; ?></strong> does not remove any database data that may have been created. To completely remove it, you can uninstall it here.</p>
	<p>This option can also be used if you want to reset <strong><?php echo $uninstallArgs['name']; ?></strong>'s options to their default.</p>
	<p>&nbsp;</p>
	<p style="color: red"><strong>WARNING:</strong><br />Once uninstalled, this cannot be undone. You should backup your Wordpress database first.</p>
	<p style="color: red"><strong>The following WordPress Options Data will be DELETED:</strong></p>
	<table class="widefat hikari-uninstall-table">
		<thead>
			<tr>
<?php
if(isset($uninstallArgs['options']))
foreach($uninstallArgs['options'] as $option){
	switch($option['opType']){
		case 'sitemeta': echo '<th>Multisite Options</th>'; break;
		case 'usermeta': echo '<th>User specific Options</th>'; break;
		case 'wp_options': echo '<th>WordPress Options</th>'; break;
		case 'postmeta': echo '<th>WordPress Post Metadata</th>'; break;
		case 'commentmeta': echo '<th>WordPress Comments Metadata</th>'; break;
		case 'tables': echo '<th>Tables</th>'; break;
		default: echo '<th>Options</th>';
	}
}
?>
			</tr>
		</thead>
		<tr>
<?php
if(isset($uninstallArgs['options']))
foreach($uninstallArgs['options'] as $option){
	//$opType = $option['opType'];
?>
			<td valign="top">
				<ol>
<?php
	foreach($option['itemNames'] as $itemName){
		echo '<li>'.$itemName;
/*
opType to implement:
- sitemeta
- usermeta

implement network-wide options interface using sitemeta
*/

		switch($option['opType']){
			case 'sitemeta':
				$exists = get_site_option($itemName);
				$exists = !empty($exists);
				if($exists){
					echo ' <span style="color: green">It\'s on database at the moment.</span>';
				}else{
					echo ' <span style="color: red">Option not on database, default is being used.</span>';
				}



				break;
			case 'wp_options':
				$exists = get_option($itemName);
				$exists = !empty($exists);
				if($exists){
					echo ' <span style="color: green">It\'s on database at the moment.</span>';
				}else{
					echo ' <span style="color: red">Option not on database, default is being used.</span>';
				}



				break;
			case 'usermeta':
				$exists = $this->hasUserMetaKey($itemName);
				if($exists){
					echo ' <span style="color: green">It\'s on database at the moment.</span>';
				}else{
					echo ' <span style="color: red">Option not on database, database is empty</span>';
				}
			
			
			
			
				break;
			case 'postmeta':
				$exists = $this->hasPostMetaKey($itemName);
				if($exists){
					echo ' <span style="color: green">It\'s on database at the moment.</span>';
				}else{
					echo ' <span style="color: red">Option not on database, database is empty</span>';
				}


				break;
			case 'commentmeta':
				$exists = $this->hasCommentMetaKey($itemName);
				if($exists){
					echo ' <span style="color: green">It\'s on database at the moment.</span>';
				}else{
					echo ' <span style="color: red">Option not on database, database is empty</span>';
				}
			
			
			
			
				break;
			case 'tables':
			
			
			
			
			
			
			
				break;
		}
		

		echo "</li>\n";
	}
?>
				</ol>
			</td>
<?php } ?>
		</tr>
	</table>
	<p>&nbsp;</p>
	<p style="text-align: center;">
		<?php //<input type="hidden" name="page" value="< ?php echo $this->optionsName; ? >" /> ?>
		<input type="radio" name="reset_full" value="yes" /> Yes, Reset all options<br />
		<input type="radio" name="reset_full" value="no" checked="checked" /> No, don't reset options<br /><br />
		<input type="submit" value="UNINSTALL <?php echo $uninstallArgs['name']; ?>" class="button" onclick="return confirm('You are about to Reset \'<?php echo $uninstallArgs['name']; ?>\' options from WordPress database.\nTHIS ACTION IS NOT REVERSIBLE.\n\n Next page you will be able to verify if options reseting was successiful and finish the uninstalling.\n\n Choose [Cancel] To Stop, [OK] To Reset.')" />
	</p>
</div> 
</form>
<?php
	}
//

	public static function hasUserMetaKey($metaKey){
		global $wpdb;
		$user_id = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT user_id FROM $wpdb->usermeta WHERE meta_key = %s LIMIT 1", $metaKey));
		return !empty($user_id);
	}
	public static function hasPostMetaKey($metaKey){
		global $wpdb;
		$post_id = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT post_id FROM $wpdb->postmeta WHERE meta_key = %s LIMIT 1", $metaKey));
		return !empty($post_id);
	}
	public static function hasCommentMetaKey($metaKey){
		global $wpdb;
		$comment_id = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT comment_id FROM $wpdb->commentmeta WHERE meta_key = %s LIMIT 1", $metaKey));
		return !empty($comment_id);
	}

	public static function delete_user_meta_by_key($user_meta_key){
		if( !$user_meta_key )
			return false;

		global $wpdb;
		$user_ids = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT user_id FROM $wpdb->usermeta WHERE meta_key = %s", $user_meta_key));
		if( $user_ids ){
			foreach ( $user_ids as $user_id )
				delete_user_meta($user_id,$user_meta_key);
			return true;
		}
		return false;
	}
	public static function delete_comment_meta_by_key($comment_meta_key){
		if( !$comment_meta_key )
			return false;

		global $wpdb;
		$comment_ids = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT comment_id FROM $wpdb->commentmeta WHERE meta_key = %s", $comment_meta_key));
		if( $comment_ids ){
			$commentmetaids = $wpdb->get_col( $wpdb->prepare( "SELECT meta_id FROM $wpdb->commentmeta WHERE meta_key = %s", $comment_meta_key ) );
			$in = implode( ',', array_fill(1, count($commentmetaids), '%d'));
			do_action( 'delete_commentmeta', $commentmetaids );
			$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->commentmeta WHERE meta_id IN($in)", $commentmetaids ));
			do_action( 'deleted_commentmeta', $commentmetaids );
			foreach ( $comment_ids as $comment_id )
				wp_cache_delete($comment_id, 'comment_meta');
			return true;
		}
		return false;
	}




	public function resetOptions($uninstallArgs){
		echo '<div class="wrap">';
		echo '<h2>Uninstall '.$uninstallArgs['name'].'</h2>';
		echo '<div class="updated fade">';
		
		foreach($uninstallArgs['options'] as $option){
			switch($option['opType']){
				case 'sitemeta':

					foreach($option['itemNames'] as $itemName){
						$deleted = delete_site_option($itemName);
						
						$exists = get_site_option($itemName);
						$exists = !empty($exists);
					
						echo '<p>';
						if($deleted){
							echo '<span style="color: green;">Multisite Options (sitemeta) row <strong>'.$itemName.'</strong> has been deleted.</span>';
						}else{
							echo '<span style="color: red;">Multisite Options (sitemeta) row <strong>'.$itemName.'</strong> was not deleted.</span>';
						}
						
						if($exists){
							echo ' <span style="color: red; text-decoration: underline;">It still exists on database.</span>';
						}else{
							echo ' <span style="color: green; text-decoration: underline;">It\'s not on database anymore.</span>';
						}
						echo '</p>';
					}

				
					break;
				case 'usermeta':
				
					foreach($option['itemNames'] as $itemName){
						$deleted = $this->delete_user_meta_by_key($itemName);
						
						$exists = $this->hasUserMetaKey($itemName);
					
						echo '<p>';
						if($deleted){
							echo '<span style="color: green;">User specific options rows <strong>'.$itemName.'</strong> have been deleted.</span>';
						}else{
							echo '<span style="color: red;">User specific options rows <strong>'.$itemName.'</strong> were not deleted.</span>';
						}
						
						if($exists){
							echo ' <span style="color: red; text-decoration: underline;">They still exist on database.</span>';
						}else{
							echo ' <span style="color: green; text-decoration: underline;">They\'re not on database anymore.</span>';
						}
						echo '</p>';
					}
				
				
				
					break;
				case 'wp_options':

					foreach($option['itemNames'] as $itemName){
						$deleted = delete_option($itemName);
						
						$exists = get_option($itemName);
						$exists = !empty($exists);
					
						echo '<p>';
						if($deleted){
							echo '<span style="color: green;">wp_options row <strong>'.$itemName.'</strong> has been deleted.</span>';
						}else{
							echo '<span style="color: red;">wp_options row <strong>'.$itemName.'</strong> was not deleted.</span>';
						}
						
						if($exists){
							echo ' <span style="color: red; text-decoration: underline;">It still exists on database.</span>';
						}else{
							echo ' <span style="color: green; text-decoration: underline;">It\'s not on database anymore.</span>';
						}
						echo '</p>';
					}

				
					break;
				case 'postmeta':
				
					foreach($option['itemNames'] as $itemName){
						$deleted = delete_post_meta_by_key($itemName);
						
						$exists = $this->hasPostMetaKey($itemName);
					
						echo '<p>';
						if($deleted){
							echo '<span style="color: green;">PostMeta rows <strong>'.$itemName.'</strong> have been deleted.</span>';
						}else{
							echo '<span style="color: red;">PostMeta rows <strong>'.$itemName.'</strong> were not deleted.</span>';
						}
						
						if($exists){
							echo ' <span style="color: red; text-decoration: underline;">They still exist on database.</span>';
						}else{
							echo ' <span style="color: green; text-decoration: underline;">They\'re not on database anymore.</span>';
						}
						echo '</p>';
					}
				
				
					break;
				case 'commentmeta':
				
					foreach($option['itemNames'] as $itemName){
						$deleted = $this->delete_comment_meta_by_key($itemName);
						
						$exists = $this->hasCommentMetaKey($itemName);
					
						echo '<p>';
						if($deleted){
							echo '<span style="color: green;">CommentMeta rows <strong>'.$itemName.'</strong> have been deleted.</span>';
						}else{
							echo '<span style="color: red;">CommentMeta rows <strong>'.$itemName.'</strong> were not deleted.</span>';
						}
						
						if($exists){
							echo ' <span style="color: red; text-decoration: underline;">They still exist on database.</span>';
						}else{
							echo ' <span style="color: green; text-decoration: underline;">They\'re not on database anymore.</span>';
						}
						echo '</p>';
					}
				
				
				
					break;
				case 'tables':
				
				
				
				
				
				
				
					break;

			}
		
		
		
		}
		echo '</div>';		
//


		if(!empty($uninstallArgs['plugin_basename'])){
			$deactivate_url = 'plugins.php?action=deactivate&amp;plugin='.$uninstallArgs['plugin_basename'];
			$deactivate_url = wp_nonce_url($deactivate_url, 'deactivate-plugin_'.$uninstallArgs['plugin_basename']);
			$deactivate_url .= '&amp;plugin_status=inactive';

			echo '<p><strong><a href="'.$deactivate_url.'">Click Here</a> To finish the Uninstallation and <em style="text-decoration: underline">'.$uninstallArgs['name'].'</em> will be deactivated.</strong></p>';
		}
		echo '</div>';
		
		$this->loadAllOptions();
	
	}





// -------------------------------------
//	Options Page tools
// -------------------------------------

	public function resetRequrested(){
		if( $_REQUEST['reset'] == "yes" ){
			echo '<div class="updated fade">';
			
			$deleted = delete_option($this->optionsDBName);
			
			$exists = get_option($this->optionsDBName);
			$exists = !empty($exists);
		
			echo '<p>';
			if($deleted){
				echo '<span style="color: green;">wp_options row <strong>'.$this->optionsDBName.'</strong> has been deleted.</span>';
			}else{
				echo '<span style="color: red;">wp_options row <strong>'.$this->optionsDBName.'</strong> was not deleted.</span>';
			}
			
			if($exists){
				echo ' <span style="color: red; text-decoration: underline;">It still exists on database.</span>';
			}else{
				echo ' <span style="color: green; text-decoration: underline;">It\'s not on database anymore.</span>';
			}
			echo '</p>';


			echo "\n</div>\n";
			
			$this->loadAllOptions();

		// requested to reset options from uninstall form
		}elseif( $_REQUEST['reset_full'] == "yes" ){
			self::resetOptions($this->uninstallArgs);
			echo '<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>';
		}elseif( $_REQUEST['reset'] == "no" ){ ?>
			<div id="message" class="updated fade"><p style="font-weight: bold">No options reseted...</p></div>
		<?php }
	
	}

	public function debugRequestParameters(){
		if($this->debug){
			$this->dump($this->optionsDBValue,"optionsDBValue:");
			$this->dump($this->opStructure,"opStructure:");
			$this->dump(get_option($this->optionsDBName),'get_option(optionsDBName):');
			$this->dump($_REQUEST,'$_REQUEST:');
		}
	}
	
/*
	$dbGroup,// = $this->optionsDBGroup,
	$opStructure,// = $this->opStructure,
	$opDBName,// = $this->optionsDBName,
	$opDBValue// = $this->optionsDBValue
	
	If the plugin has no option to be used (exemple, you only want uninstall form), overwrite this method and blank it or pass empty $opStructure
	It can also be overwritten to custom options form or add more stuff to it (in this case call parent method to use original code).
*/
	
	public function optionsBoxForm($args){
		$defaults = array(
			'dbGroup'		=> 'undefined_group',
			'opStructure'	=> null,
			'opDBName'		=> $this->optionsDBName,
			'opDBValue'		=> $this->optionsDBValue,
			
			'formAction'	=> "options.php",
			'opType'		=> null,
			'opName'		=> $this->optionsName . ' options',
			
			'extraOptions'	=> null,
			'extraOptionsArgs' => null
		
		);
	
		$args = wp_parse_args($args,$defaults);
		extract($args);

		if(empty($opStructure)) return;


	
?>
<div class="hikari-postbox"><div class="inside">
	<form class="hikari-form" method="post" action="<?php echo $formAction; ?>">

<?php
		$_SERVER['REQUEST_URI'] = admin_url("options-general.php?page=".$this->optionsName);
		settings_fields($dbGroup);
?>

		<input type="hidden" name="<?php echo $opDBName; ?>[dbVersion]" value="<?php echo $opDBValue['dbVersion']; ?>" />
<?php
if($opType)
	echo "		<input type='hidden' name='opType' value='".$opType."' />\n";

$this->print_admin_options($opStructure,$opDBName,$opDBValue);

if(!empty($extraOptions))
	call_user_func($extraOptions,$extraOptionsArgs);
?>

	<p class="submit" style="text-align:center;">
		<input type="submit" name="submit" value="<?php _e('Save Settings') ?>"  title="This will store the settings to the database." />
		<a class="button submit" href="?page=<?php echo $this->optionsName; ?>&amp;reset=yes" title="This will remove the settings from the database, giving you the factory defaults" onclick="return confirm('You are about to Reset \'<?php echo $opName; ?>\' from WordPress database.\nTHIS ACTION IS NOT REVERSIBLE.\n\n Choose [Cancel] To Stop, [OK] To Reset.')"><?php _e('Reset') ?></a>
	</p>

	</form>
</div></div>
<?php
	}
	
	public function optionsBoxFormDefault($flag="default"){
		$this->optionsBoxForm(array(
			'dbGroup'		=> $this->optionsDBGroup,
			'opStructure'	=> $this->opStructure,
		));
	}

	// based on Ajay's Where did They  Go From Here's option sidebar
	public function optionsSidebar(){?>

<div class="hikari-sidebar">

	<div class="widget">
		<div class="title"><?php echo $this->optionspageName; ?> Links</div>
		<ul>
			<li><a href="<?php echo $this->pluginURL; ?>" title="<?php echo $this->optionspageName; ?> page"><?php echo $this->optionspageName; ?> page</a></li>
			<?php $this->pluginLinks(); ?>
			<li><a href="http://Hikari.ws/wordpress/" title="A list of all Wordpress plugins Hikari have developed :)">Hikari's plugins</a></li>
			<li><a href="http://Hikari.ws" title="Hikari WebSite">Hikari WebSite</a></li>
			<li><a href="http://Hikari.ws/notas/742/follow-me-on-twitter/" title="Hikari on Twitter">Hikari on Twitter</a></li>
		</ul>
	</div>
	
	<div class="widget">
	<div class="title">Recent developments</div>				
<?php
require_once(ABSPATH . WPINC . '/rss.php');
wp_widget_rss_output($this->devFeed,
			array('items' => 6, 'show_author' => 0, 'show_date' => 1));
?>
	</div>

<?php $this->supportMyPluginsWidget(); ?>


</div>

<?php	}


	public function supportMyPluginsWidget(){
?>
	<div class="widget">
	<div class="title">Support my plugins</div>				
		<div class="donate">
		<p>I share my plugins in the wish of contributing with the community.</p>
		<p>But when plugin developers earn money from their fair work, we work harder and try to improve our quality.</p>
		<p>&nbsp;</p>
		<p>If you liked my plugin, please show your love and help me keep it :)</p>
		
		<form target="_blank" action="https://www.paypal.com/cgi-bin/webscr" method="post">
<fieldset>
<input type="hidden" name="cmd" value="_s-xclick"/>
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----"/>
<input type="image" src="http://img231.imageshack.us/img231/4354/paypaldonate.gif" name="submit" alt="Donate by Paypal" title="Donate by Paypal"/>
</fieldset>
</form>
		</div>
	</div>
<?php
	}


	public function options_page(){
?>
<div class="wrap">

<?php
	
	
	$this->resetRequrested();
?>

<h2><a href="?page=<?php echo $this->optionsName; ?>"><?php echo $this->optionspageName; ?> Options</a></h2>

<?php 
		$this->optionsSidebar();
?>
<div class="hikari-content">
<?php
		//$this->debugRequestParameters();

		$this->optionsBoxFormDefault();
?>

<p>&nbsp;</p>
<p>&nbsp;</p>

<?php
		$this->options_page_middle();
		
		$this->debugRequestParameters();

		$this->uninstallForm($this->uninstallArgs);
?>
</div> <!-- HkToolsOptions::options_page()  class="hikari-content" -->
</div> <!-- HkToolsOptions::options_page()  class="wrap" -->
<?php
	}
	
	
	public function options_page_middle(){}
	public function pluginLinks(){}
	//public function extraOptions(){}
	protected function options_version_verify($dbName,$dbValue){
		//$dbValue['dbVersion'] = $this->optionsDBVersion;
		return $dbValue; 
	}
	
	
	
	
	
	
	
	
	


}

