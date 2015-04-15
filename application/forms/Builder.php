<?php
/**
 * 
 * @author Ivan Rodriguez
 *
 */
class Form_Builder {
	static protected function createHtmlAttributes($attributes = array()){
		if(!is_array($attributes) || count($attributes)==0){
			return '';
		}
	
		foreach($attributes as $key=>$value){
			$parsed[]= $key.'="'. $value. '"';
		}
	
		return implode(" ", $parsed);
	
	
	}
	
	static protected function id_for($name, $value = null){
		// check to see if we have an array variable for a field name
		if (strstr($name, '['))
		{
			$name = str_replace(array('[]', '][', '[', ']'), array((($value != null) ? '_'.$value : ''), '_', '_', ''), $name);
		}
	
		return $name;
	}
	
	static function hidden_tag($name, $value='', $options = array()){
		$options = array_merge(array('type'=>'hidden'), $options);
		return self::input_tag($name, $value, $options);
	}
	
	static function input_tag($name, $value='', $options=array()){
		$id = self::id_for($name);
		$attr = self::createHtmlAttributes( array_merge( array('name' => $name, 'id'=> $id
				, 'type' => 'text' //we can override this with 'password'
				, 'value' => $value
		),
				$options)); //allows to override 'name' and 'id' with options
	
		return '<input '. $attr .' >';
	}
	
	static function select_tag( $name, $options_tags=null, $options=array(),$is_multiple=false ){
		$attr = self::createHtmlAttributes($options);
		$id = self::id_for($name);
		$input = '<select name="'.$name.( ($is_multiple)?'[]':'' ).'" id="' . $id .'" '. $attr .' >';
		$input .= $options_tags;
		$input .= '</select>';
		return $input;
	}
	
	static function options_for_select($options=array(), $selected=''){
		$output = '';
	
		foreach ($options as $keys=>$values){
	
			if (is_array($values)){
				//Create option group
				$output.= '<optgroup label="'. $keys . '">';
				foreach ($values as $oid => $ovalue){
					$output .= self::parse_select_value($oid, $ovalue, $selected);
				}
				$output.= '</optgroup>';
			}
			else {
				$output .= self::parse_select_value($keys, $values, $selected);
			}
		}
		return $output;
	}
	
	static function parse_select_value($key, $value, $selected=''){
		$output = '';
		$flagSelected = '';
		if (is_array($selected)){
			$flagSelected = array_search($key, $selected)!==false;
		}else{
			$flagSelected = $key == $selected;
		}
	
		$output .= '<option value="'.$key. '" ';
		$output .= $flagSelected?'selected':'';
		$output .= '>'.$value.'</option>';
		return $output;
	}
}