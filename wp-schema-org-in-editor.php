<?php 
	
	// http://www.engfers.com/2008/10/16/how-to-allow-stripped-element-attributes-in-wordpress-tinymce-editor/
	// https://vip.wordpress.com/documentation/register-additional-html-attributes-for-tinymce-and-wp-kses/
	
	// global $allowedposttags, $allowedtags
	
	/* 
		Plugin Name: Schema.Org Markup it WP Editor
		Plugin URI: https://github.com/Hube2/wp-schema-org-in-editor
		Description: Allow Schema Markup in the WP Content Editor
		Version: 0.0.1
		Author: John A. Huebner II
		Author URI: https://github.com/Hube2/
		License: GPL
	*/
	
	// If this file is called directly, abort.
	if (!defined('WPINC')) {die;}
	
	new blunt_schema_data();
	
	class blunt_schema_data {
		
		private $extended_elements = array(
			// element => array(attribute, attribute);
			'a' => array(
				'itemprop' => true,
				'content' => true,
			),
			'acronym' => array(
				'itemprop' => true,
				'content' => true,
			),
			'article' => array(
				'itemprop' => true,
				'content' => true,
			),
			'audio' => array(
				'itemprop' => true,
				'content' => true,
			),
			'blockquote' => array(
				'itemprop' => true,
				'content' => true,
			),
			'caption' => array(
				'itemprop' => true,
				'content' => true,
				'datetime' => true,
			),
			'cite' => array(
				'itemprop' => true,
				'content' => true,
			),
			'dd' => array(
				'itemprop' => true,
				'content' => true,
				'datetime' => true,
			),
			'div' => array(
				'itemscope:itemscope' => true,
				'itemtype' => true,
				'itemprop' => true,
				'datetime' => true,
				'content' => true,
			),
			'dt' => array(
				'itemprop' => true,
				'content' => true,
				'datetime' => true,
			),
			'figure' => array(
				'itemprop' => true,
				'content' => true,
			),
			'h1' => array(
				'itemprop' => true,
				'content' => true,
			),
			'h2' => array(
				'itemprop' => true,
				'content' => true,
			),
			'h3' => array(
				'itemprop' => true,
				'content' => true,
			),
			'h4' => array(
				'itemprop' => true,
				'content' => true,
			),
			'h5' => array(
				'itemprop' => true,
				'content' => true,
			),
			'h6' => array(
				'itemprop' => true,
				'content' => true,
			),
			'img' => array(
				'itemprop' => true,
				'content' => true,
			),
			'li' => array(
				'itemprop' => true,
				'content' => true,
				'datetime' => true,
			),
			'link' => array(
				'itemprop' => true,
				'href' => true,
				'datetime' => true,
				'content' => true,
			),
			'meta' => array(
				'itemprop' => true,
				'datetime' => true,
				'content' => true,
			),
			'p' => array(
				'itemprop' => true,
				'datetime' => true,
				'content' => true,
			),
			'span' => array(
				'itemprop' => true,
				'datetime' => true,
				'content' => true,
			),
			'time' => array(
				'itemprop' => true,
				'datetime' => true,
				'content' => true,
			),
			'track' => array(
				'itemprop' => true,
				'content' => true,
			),
			'video' => array(
				'itemprop' => true,
				'content' => true,
			),
		);
		
		public function __construct() {
			add_action('init', array($this, 'init'), 99);
			add_filter('tiny_mce_before_init', array($this, 'tiny_mce_before_init'), 99, 2);
		} // end public function __construct
		
		public function init() {
			global $allowedposttags;
			$allowedposttags = $this->merge_elements($allowedposttags, $this->extended_elements);
			//echo '<pre>'; print_r($allowedposttags); die;
			foreach ($this->extended_elements as $element => $attributes) {
				if (isset($allowedposttags[$element]) && is_array($allowedposttags[$element])) {
					array_merge($allowedposttags[$element], $attributes);
				} else {
					$allowedposttags[$element] = $attributes;
				}
			}
		} // end public function init
		
		public function tiny_mce_before_init($mce, $editor_id) {
			global $allowedposttags;
			$extended_elements = array();
			if (isset($mce['extended_valid_elements'])) {
				$extended_elements = $this->extract_elements($mce['extended_valid_elements']);
			}
			$extended_elements = $this->merge_elements($extended_elements, $this->extended_elements);
			$extended_elements = $this->merge_extended_elements($extended_elements, $allowedposttags);
			$extended_elements = $this->format_elements($extended_elements);
			$mce['extended_valid_elements'] = $extended_elements;
			return $mce;
		} // end public function tiny_mce_before_init
		
		private function format_elements($elements) {
			//echo '<pre>'; print_r($elements); echo '</pre>';
			$list = array();
			foreach ($elements as $element => $attributes) {
				$sub_list = array();
				if (count($attributes)) {
					foreach ($attributes as $attribute => $value) {
						$sub_list[] = $attribute;
					}
				}
				$list[] = $element.'['.implode('|', $sub_list).']';
			} // end foreach $elements
			//echo '<pre>'; print_r($list); echo '</pre>';
			$string = implode(',', $list);
			return $string;
		} // end private function format_elements
		
		private function merge_extended_elements($array1, $array2) {
			// merge existing allowed elements into extented elements
			foreach ($array2 as $element => $attributes) {
				if (!isset($array1[$element])) {
					continue;
				}
				foreach ($attributes as $attribute => $value) {
					if (!isset($array1[$element][$attribute])) {
						if ($attribute == 'xml:lang') {
							$attribute = 'xml';
						}
						$array1[$element][$attribute] = $value;
					}
				}
			}
			return $array1;
		} // end private function merge_extended_elements
		
		private function merge_elements($array1, $array2) {
			foreach ($array2 as $element => $attributes) {
				//echo $element; echo ' : '; print_r($attributes); echo ' : '; print_r($array1[$element]);echo '<br>';
				if (!isset($array1[$element])) {
					$array1[$element] = $attributes;
				} else {
					if (count($attributes)) {
						foreach ($attributes as $attribute => $value) {
							$array1[$element][$attribute] = $value;
						}
					}
				}
			}
			return $array1;
		} // end private function merge_elements
		
		private function extract_elements($string) {
			$elements = array();
			if (trim($string) == '') {
				return $elements;
			}
			$tags = explode(',', $string);
			foreach ($tags as $tag) {
				if (strpos($tag, '[') === false) {
					$elements[trim($tag)] = array();
					continue;
				} else {
					$attributes = explode('[', trim($tag, ']'));
					$element = trim(array_shift($attributes));
					$elements[$element] = array();
					foreach ($attributes as $attribute) {
						$elements[$element][trim($attribute)] = true;
					}
				}
			}
			return $elements;
		} // end private function extract_elements
		
		private function write_to_file($value) {
			// this function for testing & debuggin only
			return;
			$file = dirname(__FILE__).'/data.txt';
			$handle = fopen($file, 'a');
			ob_start();
			//echo "\r\n";
			//echo "\r\n\r\nvar_dump:: "; var_dump($value); echo " ::end var_dump\r\n\r\n";
			if (is_array($value) || is_object($value)) {
				print_r($value);
			} elseif (is_bool($value)) {
				var_dump($value);
			} else {
				echo $value;
			}
			echo "\r\n\r\n";
			fwrite($handle, ob_get_clean());
			fclose($handle);
		} // end private function write_to_file
		
	} // end class blunt_schema_data
	
	
?>