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
	 * @param  array $config
	 * @param  array $result
	 * @return array
	 */
	static private function _Difference($config, $current)
	{
		//	...
		$blueprint['databases']	 = self::_DifferenceDatabase($config, $current);
		$blueprint['tables']	 = self::_DifferenceTable($config, $current, $blueprint);
		$blueprint['field']		 = self::_DifferenceField($config, $current, $blueprint);

		//	...
		return $blueprint;
	}

	/** _DifferenceDatabase
	 *
	 * @param  array $config
	 * @param  array $result
	 * @return array
	 */
	static private function _DifferenceDatabase($config, $current)
	{
		//	...
		$result = null;

		//	...
		foreach( ifset($config['databases'], []) as $name => $conf ){
			$result[$name] = isset($current['databases'][$name]) ? true: false;
		}

		//	...
		return $result;
	}

	/** _DifferenceTable
	 *
	 * @param  array $config
	 * @param  array $result
	 * @return array
	 */
	static private function _DifferenceTable($config, $current, $blueprint)
	{
		//	...
		$result = null;

		//	...
		foreach( ifset($blueprint['databases'], []) as $db_name => $io ){
			if(!$io){
				continue;
			}

			//	...
			foreach( ifset($config['databases'][$db_name]['tables'],[]) as $name => $conf ){
				$result[$db_name][$name] = isset($current['databases'][$db_name]['tables'][$name]) ? true: false;
			}
		}

		//	...
		return $result;
	}

	/** _DifferenceField
	 *
	 * @param  array $config
	 * @param  array $result
	 * @return array
	 */
	static private function _DifferenceField($config, $current, $blueprint)
	{
		//	...
		$result = null;

		//	...
		foreach( ifset($blueprint['tables'], []) as $db_name => $temp ){
			foreach($temp as $table_name => $io){
				if(!$io){
					continue;
				}

				foreach( ifset($config['databases'][$db_name]['tables'][$table_name]['fields'],[]) as $name => $conf ){
					$result[$db_name][$name] = isset($current['databases'][$db_name]['tables'][$table_name]['fields'][$name]) ? true: false;
				}
			}
		}

		//	...
		return $result;
	}

	/** Inspection
	 *
	 * @param  array $configs
	 * @return array
	 */
	static function Inspection($configs)
	{
		//	...
		$result = [];

		//	...
		foreach( $configs as $config ){
			//	...
			$prod = $config['driver'];
			$host = $config['host'];
			$port = $config['port'];
			$user = $config['user'];
			$key  = "$prod:$host:$port:$user";
			$current = self::_Inspection($config);

			//	...
			$blueprint = self::_Difference($config, $current);
		}

		//	...
		return $blueprint;
	}
}