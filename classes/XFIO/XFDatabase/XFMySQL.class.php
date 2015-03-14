<?php
//xFacility2014
//XFMySQL(1.2.0.)
//Studio2b
//Michael Son(michaelson@nate.com)
//22JUN2014(1.0.0.) - This file is rewrited for xFacility2014. It's not compatible with xFacility2012.
//27JUN2014(1.1.0.) - condition() is added.
//29JUN2014(1.2.0.) - select(), insert(), update() and delete() are added.
//01DEC2014(1.3.0.) - Data format of "ETC" field is changed from xFTableString to JSON. insert(), update() - partially updating of ETC field data is available now, condition() - bug, getTable() - Auto-converting of ETC field data, 

require_once($_SERVER['DOCUMENT_ROOT']."/xfacility/classes/XFIO/XFDatabase/XFDatabaseEngine.class.php");

class XFMySQL extends XFDatabaseEngine {
	function XFMySQL() {
		$this->XFDatabaseEngine();
		//echo "XFMySQL Ready<br />\n";
	}

	function connect() {
		$return = mysql_connect($this->server, $this->username, $this->password);
		if($return) {
			//Select database
			mysql_select_db($this->database, $return);
			$this->link = $return;
			return $this->link;
		} else {
			return false;
		}
	}
	function disconnect() {
		mysql_close($this->link);
		unset($this->link);
	}

	function getColumns($tool, $table) {
		unset($this->counter);
		$this->query = "SHOW FIELDS FROM `".$this->prefix."_".$tool."_".$table."`";
		$result = $this->query();
		if($result) {
			$i=0;
			while ($row = mysql_fetch_array($result)) {
				$return[$i] = $row['Field'];
				$i++;
			}
		} else {
			$return = false;
		}
		return $return;
	}

	function query($query) {
		unset($this->counter);
		if($query!=NULL) {
			$this->query = $query;
		} else if($this->query!=NULL) {
			//$this->query = $this->query;
		} else {
			//Nothing to do
			return false;
		}
		if($this->link==NULL) {
			$this->connect();
		} else {
			$this->disconnect();
			$this->connect();
		}
		$result = @mysql_query($this->query, $this->link);
		if(!$result) {
			return false;
		}
		if(strpos(strtolower($this->query),"select")!==false) {
			$this->counter = mysql_num_rows($result);
			$return = $this->getTable($result);
		} else {
			$return = $result;
		}
		return $return;
	}

	function getTable($result, $fields = NULL) {
		if(!$result) {
			return false;
		}
		//If the list of fields are missed,
		if($fields == NULL) {
			$counter = 0;
		} else {
			if(is_array($fields)) {
				$temp = $fields;
				$counter = count($fields) - 1;
			} else {
				//Parse fields by comma
				$temp = split(",", $fields);
				//Estimate times for a loop
				$counter = substr_count($fields, ",");
			}
		}
		for($i=0; $i<=$counter; $i++) {
			//If the list of fields are missed,
			if($fields == NULL) {
				$field = @mysql_field_name($result, $i);
				//If there is no field name,
				if ($field == NULL) {
					//Stop this Loop
					break;
				} else {
					//One more time
					$counter++;
				}
			} else {
				$field = $temp[$i];
			}
			//Estimate times for a subloop
			$counter2 = mysql_num_rows($result);
			for($j=0; $j<$counter2; $j++) {
				if($field=="etc") {
					$etc = mysql_result($result, $j, $field);
					$temp = json_decode($etc, true);
					foreach($temp as $key => $value) {
						$return[$j][$key] = $value;
					}
				} else {
					$return[$j][$field] = mysql_result($result, $j, $field);
				}
			}
		}
		//Return Array
		return $return;
	}

	function condition($condition) {
		//Michael Son(michaelson@nate.com) - 27JUN2014
		if(!is_null(json_decode($condition, true)))
			$condition = json_decode($condition, true);
		if(strtolower($condition)=="all") {
			//All
			$return = 1;
		} else if(is_numeric($condition)) {
			//Number
			$return = sprintf("`no` = '%s'", $condition);
		} else if((!is_array($condition) && strpos($condition, ";")!==false) || is_array($condition)) {
			if(!is_array($condition) && strpos($condition, ";")!==false) {
				//xFTableString
				$tableClass = new XFTable();
				$temp = $tableClass->import($condition);
				unset($condition, $tableClass);
				$condition = $temp;
			}
			unset($temp);
			//xFTableArray
			for($i=0; $i<=count($condition); $i++) {
				if(is_null($condition[$i])) {
					break;
				} else if(!is_array($condition[$i])){
					continue;
				} else if(!is_null($return)) {
					$return .= " OR ";
				}
				foreach($condition[$i] as $column => $value) {
					if(strpos($column, ".")===false) {
						$column = sprintf("`%s`", $column);
					} else {
						$temp = explode(".", $column);
						unset($column);
						foreach($temp as $val) {
							if(!is_null($column))
								$column .= ".";
							$column .= sprintf("`%s`", $val);
						}
					}
					if(is_array($value)) {
						continue;
					} else if(!is_null($andCondition)) {
						$andCondition .= " AND ";
					}
					if(substr($value, 1)=="%" || substr($value, -1)=="%") {
						$andCondition .= sprintf("%s LIKE '%s'", addslashes($column), addslashes($value));
					} else {
						if($column==password) {
							$andCondition .= sprintf("%s=password('%s')", addslashes($column), addslashes($value));
						} else {
							$andCondition .= sprintf("%s='%s'", addslashes($column), addslashes($value));
						}
					}
				}
				$return .= "(".$andCondition.")";
				unset($andCondition);
			}
		} else {
			$return = false;
		}
		return $return;
	}

	function insert($tool, $table, $data) {
		//Michael Son(michaelson@nate.com) - 29JUN2014
		//Michael Son(michaelson@nate.com) - 17JUL2014 - ON DUPLICATE KEY UPDATE - DELETED
		
		//Key
		$keyArray = $this->getColumns($tool, $table);
		foreach($keyArray as $value) {
			if($value=="no")
				continue;
			if(!is_null($key))
				$key .= ", ";
			$key .= sprintf("`%s`", $value);
		}
		$key = "(".$key.")";

		//Data
		if(!is_array($data) && strpos($data, ";")!==false) {
			//xFTableString
			$tableClass = new XFTable();
			$temp = $tableClass->import($data);
			unset($data, $tableClass);
			$data = $temp;
			unset($temp);
		}
		if(is_array($data)) {
			foreach($data as $row => $columns) {
				foreach($columns as $column => $value) {
					foreach($keyArray as $nowColumn) {
						if(strtolower($nowColumn)=="etc") {
							/*legacy
							if(!is_null($temp[$row]['etc']))
								$temp[$row]['etc'] .= ",";
							$temp[$row]['etc'] .= sprintf("%s:'%s'", $column, $value);
							*/
							$etc[$row][$column]=$value;
						} else if(strtolower($nowColumn)==strtolower($column)) {
							$temp[$row][$nowColumn] = $value;
							break;
						}
					}
				}
				/*legacy
				if(!is_null($temp[$row]['etc']))
					$temp[$row]['etc'] .= ";";
				*/
				if(is_array($etc[$row]))
					$temp[$row]['etc'] = json_encode($etc[$row]);
			}
				
			foreach($temp as $row => $columns) {
				//Spam Protection
				if(!is_array($this->select($tool, $table, $temp))) {
					foreach($keyArray as $column) {
						if(strtolower($column)=="no")
							continue;
						if(!is_null($rowValues))
							$rowValues .= ",";
						if(is_null($columns[$column])) {
							$rowValues .= "NULL";
						} else {
							if($column=="password") {
								$rowValues .= sprintf("password('%s')", addslashes($columns[$column]));
							} else {
								$rowValues .= sprintf("'%s'", addslashes($columns[$column]));
							}
						}
					}
					if(!is_null($values))
						$values .= ",";
					$values .= "(".$rowValues.")";
				}
				unset($rowValues);
			}
			
			//SET
			//$set = $this->set($tool, $table, $data);
			//$return = sprintf("INSERT INTO `%s` %s VALUES %s ON DUPLICATE KEY UPDATE %s;", $this->prefix."_".$tool."_".$table, $key, $values, $set);
			if(!is_null($values)) {
				$return = sprintf("INSERT INTO `%s` %s VALUES %s;", $this->prefix."_".$tool."_".$table, $key, $values);
				$this->query($return);
				$return = true;
			} else {
				$return = false;
			}
		} else {
			$return = false;
		}
		return $return;
	}

	function update($tool, $table, $data, $condition) {
		//Michael Son(michaelson@nate.com) - 29JUN2014

		//Key
		$keyArray = $this->getColumns($tool, $table);
			
		//Condition
		$where = $this->condition($condition);
		$etcData = $this->query(sprintf("SELECT `no`,`etc` FROM `%s` WHERE %s;", $this->prefix."_".$tool."_".$table, $where));
		
		if(!is_array($data) && strpos($data, ";")!==false) {
			//xFTableString
			$tableClass = new XFTable();
			$temp = $tableClass->import($data);
			unset($data, $tableClass);
			$data = $temp;
			unset($temp);
		}
		foreach($data[0] as $column => $value) {
			foreach($keyArray as $nowColumn) {
				if(strtolower($nowColumn)=="etc") {
					foreach($etcData as $etcRow => $etcColumns) {
						$etcData[$etcRow][$column] = $value;
					}
					unset($data[0][$column]);
				} else if($nowColumn==$column) {
					break;
				}
			}
		}
		if($keyArray!=false && $where!=false && is_array($data)) {
			//Set
			$set = $this->set($tool, $table, $data);
			$this->query(sprintf("UPDATE `%s` SET %s WHERE %s;", $this->prefix."_".$tool."_".$table, $set, $where));
			//echo sprintf("UPDATE `%s` SET %s WHERE %s;", $this->prefix."_".$tool."_".$table, $set, $where);
			foreach($etcData as $etcRow => $etcColumns) {
				$no = $etcColumns[no];
				unset($etcColumns[no]);
				$value = json_encode($etcColumns);
				$this->query(sprintf("UPDATE `%s` SET `etc`='%s' WHERE %s;", $this->prefix."_".$tool."_".$table, addslashes($value), "`no`=".$no));
			}
			$return = true;
		} else {
			$return = false;
		}
		return $return;
	}

	function set($tool, $table, $data) {
		//Key
		$keyArray = $this->getColumns($tool, $table);
		
		foreach($data as $row => $columns) {
			foreach($columns as $column => $value) {
				foreach($keyArray as $nowColumn) {
					if(strtolower($nowColumn)=="etc") {
						/*
						if(!is_null($temp[0]['etc']))
							$temp[0]['etc'] .= ",";
						$temp[0]['etc'] .= sprintf("%s:'%s'", $column, $value);
						*/
						$etc[0][$column] = $value;
					} else if(strtolower($nowColumn)==strtolower($column)) {
						if(is_null($temp[0][$nowColumn])) {
							$temp[0][$nowColumn] = $value;
						} else {
							/*
							 if(!is_null($temp[0]['etc']))
								$temp[0]['etc'] .= ",";
							$temp[0]['etc'] .= sprintf("%s:'%s'", $column, $value);
							*/
						}
						break;
					}
				}
			}
			if(is_array($etc[0]))
				$temp[0]['etc'] = json_encode($etc[0]);
		}

		foreach($temp as $row => $columns) {
			foreach($columns as $column => $value) {
				if(strtolower($column)=="no")
					continue;
				if(!is_null($set))
					$set .= ", ";
				if($value=="" || is_null($value)) {
					$set .= sprintf("`%s`=NULL", $column);
				} else {
					if($column=="password") {
						$set .= sprintf("`%s` = password('%s')", $column, addslashes($value));
					} else {
						$set .= sprintf("`%s` = '%s'", $column, addslashes($value));
					}
				}
			}
		}

		return $set;
	}

	function delete($tool, $table, $condition) {
		//Condition
		$where = $this->condition($condition);
		if($where!=false) {
			$this->query(sprintf("DELETE FROM `%s` WHERE %s;", $this->prefix."_".$tool."_".$table, $where));
			$return = true;
		} else {
			$return = false;
		}
		return $return;
	}

	function select($tool, $table, $condition=NULL) {
		//Condition
		$where = $this->condition($condition);
		if($where!=false) {
			$return = $this->query(sprintf("SELECT * FROM `%s` WHERE %s;", $this->prefix."_".$tool."_".$table, $where));
		} else {
			if(is_null($condition)) {
				$return = $this->query(sprintf("SELECT * FROM `%s`;", $this->prefix."_".$tool."_".$table));
			} else {
				$return = false;
			}
		}
		return $return;
	}
}
?>