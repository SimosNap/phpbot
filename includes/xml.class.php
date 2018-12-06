<?php
	if (!function_exists ('is_assoc')) {
		function is_assoc ($array) {
			return (!(array_keys ($array) === array_keys (array_keys ($array))));
		}
	}

	class xml2array {
		var $arrOutput = array();
		var $resParser;
		var $strXmlData;

		function parse($strInputXML) {
			$this->resParser = xml_parser_create ();
			xml_set_object ($this->resParser, $this);
			xml_set_element_handler ($this->resParser, "tagOpen", "tagClosed");
			xml_set_character_data_handler ($this->resParser, "tagData");
			$this->strXmlData = xml_parse ($this->resParser, $strInputXML);
			if(!$this->strXmlData) {
				die (sprintf ("XML error: %s at line %d", xml_error_string (xml_get_error_code ($this->resParser)), xml_get_current_line_number ($this->resParser)));
			}
			xml_parser_free($this->resParser);
			$xml = $this->arrOutput;
			$name = $this->arrOutput[0]["name"];
			$this->arrOutput = array ();
			for ($i = 0; $i < count ($xml[0]["children"]); $i++) {
				$tmp = $this->normalize ($xml[0]["children"][$i]);
				$this->arrOutput[$xml[0]["children"][$i]['name']] = $tmp[$xml[0]["children"][$i]['name']];
			}
			return array ($name => $this->arrOutput);
		}

		function tagOpen($parser, $name, $attrs) {
			$tag = array ("name"=>strtolower($name), "attrs"=>$attrs);
			array_push($this->arrOutput, $tag);
		}

		function tagData($parser, $tagData) {
			if(trim ($tagData)) {
				if(isset($this->arrOutput[count($this->arrOutput) - 1]['tagData'])) {
					$this->arrOutput[count ($this->arrOutput) - 1]['tagData'] .= $tagData;
				} else {
					$this->arrOutput[count ($this->arrOutput) - 1]['tagData'] = $tagData;
				}
			}
		}

		function tagClosed($parser, $name) {
			$this->arrOutput[count($this->arrOutput)-2]['children'][] = $this->arrOutput[count($this->arrOutput)-1];
			array_pop($this->arrOutput);
		}

		function normalize ($root) {
			$ret = array ();
			$ret[$root['name']]['attributes'] = array_change_key_case ($root['attrs'], CASE_LOWER);
			if (isset ($root['children'])) {
				for ($i = 0; $i < count ($root['children']); $i++) {
					if (isset ($root['children'][$i]['tagData'])) {
						$ret[$root['name']][$root['children'][$i]['name']] = $root['children'][$i]['tagData'];
					} else {
						if (!is_array ($ret[$root['name']][$root['children'][$i]['name']])) {
							$ret[$root['name']][$root['children'][$i]['name']] = array ();
						}
						array_push ($ret[$root['name']][$root['children'][$i]['name']], $this->normalize ($root['children'][$i]));
					}
				}
			}
			return $ret;
		}
	}

	class array2xml {
		var $str = '';
		var $pad = 0;
		var $encoding = '';
		var $version = '';

		function array2xml ($version = '1.0', $encoding = 'ISO-8859-1') {
			$this -> version = $version;
			$this -> encoding = $encoding;
		}

		function parse ($array) {
			$this -> str = '<?xml version="' . $this -> version . '" encoding="' . $this -> encoding . '"?>' . "\n";
			$this -> pad = 0;
			return $this -> parse_node ($array);
		}
		
		function parse_node ($array) {
			foreach ($array as $key => $value) {
				if (is_array ($value)) {
					if (is_assoc ($value)) {
						$attrs = '';
						if (isset ($value['attributes'])) {
							foreach ($value['attributes'] as $an => $av)
								$attrs .= ' ' . $an . '="' . $av .'"';
							unset ($value['attributes']);
						}
						$this -> str .= str_repeat ("\t", $this -> pad) . '<' . $key . $attrs . '>' . "\n";
						$this -> pad++;
						$this -> parse_node ($value);
						$this -> pad--;
						$this -> str .= str_repeat ("\t", $this -> pad) . '</' . $key . '>' . "\n";
					} else {
						foreach ($value as $sub) {
							$this -> parse_node ($sub);
						}
					}
				} else {
					$this -> str .= str_repeat ("\t", $this -> pad) . '<' . $key . '>' . $value . '</' . $key . '>' . "\n";
				}												  
			}
			return $this -> str;
		}
	}
?>