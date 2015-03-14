<?php
//xFacility2014
//XFTable(0.1.0.)
//Studio2b
//Michael Son(michaelson@nate.com)
//29JUN2014 - This class is newly created. import() is created.

class XFTable extends XFObject {
	var $application, $table, $tableArray;
	
	function XFTable($tableArray=NULL) {
		if(!is_null($tableArray)) {
			$this->create($tableArray);
		}
	}
	
	function create($data) {
		if(!is_array($data[0])) {
			$return = $this->import($data);
		} else {
			$return = $data;
		}
		if($return!=false)
			$this->tableArray = $return;
		return $return;
	}
	
	function browse($condition) {
		//Michael Son(michaelson@nate.com) - 28JUN2014
		//$this->tableArray = array(array("column1" => "hello", "column2" => "world"), array("column1" => "olleh", "column2" => "world"));
		//$condition = array(array("column2"=>"world"));
		//$return = array(0, 1);
		
		$conditionTable = $this->import($condition, true);
		if(is_array($this->tableArray[0])) {
			foreach($this->tableArray as $row => $columns) {
				foreach($conditionTable as $conditionRow => $conditionColumns) {
					$selectionFlag = true;
					foreach($conditionColumns as $conditionColumn => $conditionValue) {
						if($conditionValue != $columns[$conditionColumn])
							$selectionFlag = false;
					}
					if($selectionFlag==true) {
						$return[] = $row;
						break;
					}
				}
			}
		} else {
			$return = false;
		}
		return $return;
	}
	
	function import($data, $internal=false) {
		//Michael Son(michaelson@nate.com) - 28JUN2014 - It's able to import XFTableString.
		
		if(is_array($data[0])) {
			//Nothing To do
				$return = $data;
		} else if(is_array($data) && !is_array($data[0])) {
			//GET, POST
			foreach($data as $column => $rows) {
				foreach($rows as $row => $value) {
					if($value!="" && !is_null($value))
						$return[$row][$column] = $value;
				}
			}
		} else if(strpos($data, "<xml>")!==false) {
			//XFTableXML
		} else if(is_numeric($data)) {
			//XFTableString
			$return[0]['no'] = $data;
		} else if(strpos($data, ";")!==false) {
			//XFTableString
			if(substr($data, -1)!=";")
				$data .= ";";
			$dataClass = new XFString($data);
			$positionArray = $dataClass->browse(array(":", ",", ";", "'", '"'), true);
			unset($dataClass);
			$i=0;
			//echo $data."<br />\n";
			foreach($positionArray as $row => $columns) {
				//echo $columns['keyword']." ";
				if($singleFlag==true) {
					if($columns['keyword']=="'") {
						$valueFlag = true;
						$value = trim(substr($data, $singlePosition+1, $columns['position'] - ($singlePosition+1)));
						unset($singleFlag, $singlePosition);
					}
					continue;
				} else if($doubleFlag==true) {
					if($columns['keyword']=='"') {
						$valueFlag = true;
						$value = trim(substr($data, $doublePosition+1, $columns['position'] - ($doublePosition+1)));
						unset($doubleFlag, $doublePosition);
					}
					continue;
				}
			
				if($columns['keyword']=="'") {
					$singleFlag = true;
					$singlePosition = $columns['position'];
				} else if($columns['keyword']=='"') {
					$doubleFlag = true;
					$doublePosition = $columns['position'];
				} else if($columns['keyword']==":") {
					$columnFlag = true;
					if($row==0) {
						$column = trim(substr($data, 0, $columns['position']));
					} else {
						$column = trim(substr($data, $positionArray[$row-1]['position']+1, $columns['position'] - ($positionArray[$row-1]['position']+1)));
					}
			
				} else if($columns['keyword']=="," || $columns['keyword']==";") {
					if($columnFlag!=true) {
						$column = "no";
					}
					if($valueFlag==true) {
						if($column=="no" && !is_numeric($value)) {
						} else {
							$return[$i][$column] = $value;
						}
						unset($valueFlag, $value);
					} else {
						if($row==0) {
							$temp = trim(substr($data, 0, $positionArray[$row]['position']));
						} else {
							$temp = trim(substr($data, $positionArray[$row-1]['position']+1, $positionArray[$row]['position'] - ($positionArray[$row-1]['position']+1)));
						}
						if(!is_null($temp) && $temp!="") {
							if($column=="no" && !is_numeric($temp)) {
							} else {
								$return[$i][$column] = $temp;
							}
						}
					}
					unset($columnFlag, $column);
					if($columns['keyword']==";" && is_array($return[$i]))
						$i++;
				}
			}
		} else {
			$return = false;
		}
		
		if($return!=false && $internal!=true)
			$this->tableArray = $return;
		return $return;
	}
	
	function sync() {
		
	}
}
?>