<?php
/**
 * unit-self-test:/Doctor.class.php
 *
 * @creation  2017-03-12
 * @version   1.0
 * @package   unit-self-test
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** Doctor
 *
 * @creation  2017-03-12
 * @version   1.0
 * @package   unit-self-test
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Doctor
{
	/** Instantiate DB Object.
	 *
	 * @param  array $config
	 * @return DB
	 */
	static private function _init_db($config)
	{
		/* @var $db DB */
		static $pool;

		//	...
		$prod = $config['driver'];
		$host = $config['host'];
		$port = $config['port'];
		$user = $config['user'];

		//	...
		if(!$db = ifset($pool[$prod][$host][$port][$user]) ){
			//	...
			if(!$db = Unit::Factory('db') ){
				return false;
			}

			//	...
			if(!$db->Connect($config) ){
				return false;
			}

			//	Stack at pool.
			$pool[$prod][$host][$port][$user] = $db;
		}

		//	...
		return $db;
	}

	/** Instantiate SQL Object.
	 *
	 * @param  array $db
	 * @return SQL
	 */
	static private function _init_sql($db)
	{
		/* @var $sql SQL */
		static $sql;

		//	...
		if(!$sql){
			if(!$sql = Unit::Factory('sql')){
				return;
			}
		}

		//	Connect to DB-object and SQL-object, each by config.
		$sql->SetDatabase($db);

		//	...
		return $sql;
	}

	/** Each inspection by config.
	 *
	 * @param  array $config
	 * @return array
	 */
	static private function _Inspection($config)
	{
		//	...
		if( empty($config) ){
			Html::E("Empty argument. (database configuration)");
			return false;
		}

		//	...
		if(!$db = self::_init_db($config) ){
			return;
		}

		//	...
		if(!$sql = self::_init_sql($db) ){
			return;
		}

		//	...
		$result = [];
		$_db    = null;
		$_table = null;
		$_field = null;

		//	Get database name list.
		foreach( $db->Query($sql->Show()) as $temp ){
			//	...
			$result['host:port:user'] = null;

			//	...
			$db_name = $temp['Database'];
			if( $db_name === 'information_schema' ){
				continue;
			}

			//	...
			unset($_db);
			$_db = &$result['databases'][$db_name];
			$_db['index']     = null;
			$_db['comment']   = null;
			$_db['collation'] = null;

			//	Get table name list.
			foreach( $db->Query($sql->Show(['database'=>$db_name])) as $table ){
				$table_name = $table["Tables_in_{$db_name}"];

				//	...
				unset($_table);
				$_table = &$_db['tables'][$table_name];

				//	...
				$_table['comment'] = null;

				//	Get Table index.
				$indexes = $db->Query($sql->Show(['database'=>$db_name, 'table'=>$table_name, 'index'=>1]));
				$_table['indexes'] = $indexes;

				//	Get table define.
				foreach( $db->Query($sql->Show(['database'=>$db_name, 'table'=>$table_name])) as $field ){
					//	...
					$field_name = $field['Field'];

					//	...
					unset($_field);
					$_field = &$_table['fields'][$field_name];

					//	Set filed name and define.
					foreach( $field as $key => $val ){
						$key = lcfirst($key);
						if( $key === 'type' ){
							//	unsigned
							if( strpos($val, 'unsigned') ){
								$_field['unsigned'] = true;
							}
							//	length
							if( $st = strpos($val, '(') ){
								$en = strpos($val, ')');
								$length = substr($val, $st+1, $en-$st-1);
								$val    = substr($val, 0, $st);
								$_field['length'] = $length;
							}
						}else // null
						if( $key === 'null' ){
							$val = $val === 'YES' ? true: false;
						}else // key is index.
						if( $key === 'key' ){
							if( $val ){
								$val = strtolower($val);
							}else{
								continue;
							}
						}else // collation is near charset.
						if( $key === 'collation' ){
							if(!$val){
								continue;
							}
						}
						//	...
						$_field[$key] = $val;
					}
				}
			}
		}

		//	...
		return $result;
	}

	/** _Difference
	 *
	 * @param  array $inspection
	 * @param  array $config
	 * @param  array $result
	 * @return array
	 */
	static private function _Difference(&$inspection, $config, $current)
	{
		self::_DifferenceUser(    $inspection, $config, $current);
		self::_DifferenceDatabase($inspection, $config, $current);
		self::_DifferenceTable(   $inspection, $config, $current);
		self::_DifferenceField(   $inspection, $config, $current);
		self::_DifferenceStruct(  $inspection, $config, $current);
	}

	/** _DifferenceUser
	 *
	 * @param array $inspection
	 * @param array $config
	 * @param array $current
	 */
	static private function _DifferenceUser(&$inspection, $config, $current)
	{
		//	...
		$prod = $config['driver'];
		$host = $config['host'];
		$port = $config['port'];
		$user = $config['user'];

		//	...
		$inspection[$host][$prod][$port]['users'][$user] = null;
	}

	/** _DifferenceDatabase
	 *
	 * @param  array $config
	 * @param  array $result
	 * @return array
	 */
	static private function _DifferenceDatabase(&$inspection, $config, $current)
	{
		//	...
		$prod = $config['driver'];
		$host = $config['host'];
		$port = $config['port'];
		$user = $config['user'];

		//	...
		foreach( ifset($config['databases'], []) as $db_name => $conf ){
			$inspection[$host][$prod][$port]['user'][$user]['databases'][$db_name] = isset($current['databases'][$db_name ]) ? true: false;
		}
	}

	/** _DifferenceTable
	 *
	 * @param  array $config
	 * @param  array $result
	 * @return array
	 */
	static private function _DifferenceTable(&$inspection, $config, $current)
	{
		//	...
		$prod = $config['driver'];
		$host = $config['host'];
		$port = $config['port'];
		$user = $config['user'];

		//	...
		foreach( ifset($inspection[$host][$prod][$port]['user'][$user]['databases'], []) as $db_name => $io ){
			//	...
			if( $io === false ){
				continue;
			}

			//	...
			foreach( ifset($config['databases'][$db_name]['tables'],[]) as $table_name => $conf ){
				$inspection[$host][$prod][$port]['user'][$user]['tables'][$db_name][$table_name] = isset($current['databases'][$db_name]['tables'][$table_name]) ? true: false;
			}
		}
	}

	/** _DifferenceField
	 *
	 * @param  array $config
	 * @param  array $result
	 * @return array
	 */
	static private function _DifferenceField(&$inspection, $config, $current)
	{
		//	...
		$prod = $config['driver'];
		$host = $config['host'];
		$port = $config['port'];
		$user = $config['user'];

		//	...
		foreach( ifset($inspection[$host][$prod][$port]['user'][$user]['databases'], []) as $db_name => $io ){
			//	...
			if( $io === false ){
				continue;
			}

			//	...
			foreach( ifset($inspection[$host][$prod][$port]['user'][$user]['tables'][$db_name], []) as $table_name => $io ){
				//	...
				if( $io === false ){
					continue;
				}

				//	...
				foreach( ifset($config['databases'][$db_name]['tables'][$table_name]['fields'],[]) as $field_name => $conf ){
					//	...
					$io = isset($current['databases'][$db_name]['tables'][$table_name]['fields'][$field_name]) ? true: false;
					$inspection[$host][$prod][$port]['user'][$user]['fields'][$db_name][$table_name][$field_name ] = $io;
				}
			}
		}
	}

	/** _DifferenceStruct
	 *
	 * @param array $inspection
	 * @param array $config
	 * @param array $current
	 */
	static function _DifferenceStruct(&$inspection, $config, $current)
	{
		//	...
		$prod = $config['driver'];
		$host = $config['host'];
		$port = $config['port'];
		$user = $config['user'];

		//	...
		foreach( ifset($inspection[$host][$prod][$port]['user'][$user]['databases'], []) as $db_name => $io ){
			//	...
			if( $io === false ){
				continue;
			}

			//	...
			foreach( ifset($inspection[$host][$prod][$port]['user'][$user]['tables'][$db_name], []) as $table_name => $io ){
				//	...
				if( $io === false ){
					continue;
				}

				//	...
				foreach( ifset($inspection[$host][$prod][$port]['user'][$user]['fields'][$db_name][$table_name], []) as $field_name => $io ){

				}
			}
		}

		d($current['databases'][$db_name]['tables'][$table_name]['fields']);
	}

	/** Inspect
	 *
	 * @param  array $configs
	 * @return array
	 */
	static function Inspect($configs)
	{
		//	...
		$inspection = [];

		//	...
		foreach( $configs as $config ){
			//	...
			$current = self::_Inspection($config);

			//	...
			self::_Difference($inspection, $config, $current);
		}

		//	...
		return $inspection;
	}
}