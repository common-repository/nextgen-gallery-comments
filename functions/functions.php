<?php	

// FUNC: get ngg gallery id _________________________________________________________________________________________________

	if(!function_exists('rcwd_ngg_get_the_GID')){
		function rcwd_ngg_get_the_GID(){
			global $rcwd_ngg_gallery;
			if($rcwd_ngg_gallery != ''){
				return $rcwd_ngg_gallery->ID;
			}
			return false;
		}
	}

// FUNC: get ngg gallery title _________________________________________________________________________________________________

	if(!function_exists('rcwd_ngg_get_gallery_title')){
		function rcwd_ngg_get_gallery_title($gid){
			global $wpdb;
			$result = $wpdb->get_row("SELECT title FROM ".$wpdb->nggallery." WHERE gid = $gid");
			return $result->title;
		}
	}

// FUNC: get post id from custom meta _________________________________________________________________________________________________

	if(!function_exists('rcwd_get_postid_from_custom')){
		function rcwd_get_postid_from_custom($meta_key = '', $meta_value = ''){
			if($meta_key == '' and $meta_value == '') return false;
			global $wpdb;
			$meta = array();
			if($meta_key != '') 	$meta[] = " meta_key = '$meta_key'";
			if($meta_value != '') 	$meta[] = " meta_value = '$meta_value'";
			$meta 	= implode(' AND ', $meta);
			$result = $wpdb->get_results("SELECT post_id FROM $wpdb->postmeta WHERE $meta" );
			if(count($result) > 0) return (int)$result[0]->post_id;
			return false;
		}
	}

// FUNC: convert object to array _________________________________________________________________________________________________

	if(!function_exists('rcwd_objectToArray')){
		function rcwd_objectToArray($d){
			if (is_object($d)) $d = get_object_vars($d);
			if (is_array($d)){
				return array_map(__FUNCTION__, $d);
			}else{
				return $d;
			}
		} 
	} 
	
// FUNC: convert array to object _________________________________________________________________________________________________

	if(!function_exists('rcwd_arrayToObject')){
		function rcwd_arrayToObject($d) {
			if (is_array($d)) {
				return (object) array_map(__FUNCTION__, $d);
			}
			else {
				return $d;
			}
		} 
	}

// FUNC: sort multidimentional array _________________________________________________________________________________________________
		
	if(!function_exists('rcwd_multidimsort')){
		function rcwd_multidimsort($campi) {
			if( !isset($campi['array']) or ( !isset($campi['indice']) and !isset($campi['index']) ) ) return false;
			$array_in 	= $campi['array'];
			$indice 	= isset($campi['index']) 	? $campi['index'] 	: '';
			$indice 	= isset($campi['indice']) 	? $campi['indice'] 	: $indice;
			$tipo 		= isset($campi['sort']) ? $campi['sort'] : 'sort';
			$tipo 		= isset($campi['tipo']) ? $campi['tipo'] : $tipo;
			$multiarray = array();
			$array_out 	= array();
			$level 		= isset($campi['level']) ? $campi['level'] : 1;
			$loopvalue 	= 0;
			$multicount = count($array_in) - 1;
			if(rcwd_isVector($array_in)){
				for($i = 0; $i <= $multicount; $i++){
					array_push($multiarray, $array_in[$i][$indice]);
				}		
			}else{
				foreach($array_in as $key => $value){
					array_push($multiarray, $array_in[$key][$indice]);
				}		
			}
			switch($tipo){
				case 'sort':
					sort($multiarray);
					break;
				case 'arsort':
					arsort($multiarray);
					break;			
				case 'asort':
					asort($multiarray);
					break;			
			}
			reset($multiarray);	
			if(rcwd_isVector($array_in)){
				while (list ($key, $val) = each ($multiarray)) {
					$array_out[$loopvalue] = $array_in[$key];
					$loopvalue++;
				}
			}else{
				while (list ($key, $val) = each ($multiarray)) {
					foreach($array_in as $key_2 => $value_2){
						if($value_2[$indice] == $val) $array_out[$key_2] = $value_2;
					}			
				}	
			}	
			return $array_out;
		}
	}
	
	if(!function_exists('rcwd_isVector')){
		function rcwd_isVector($arr){
			return (0 !== array_reduce(
				array_keys($arr),
				'rcwd_callbackReduceNotArray',
				0
				)
			);
		}	
	}
	
	if(!function_exists('rcwd_callbackReduceNotArray')){
		function rcwd_callbackReduceNotArray($a, $b){
			return ($b === $a ? $a + 1 : 0);
		}
	}
?>