<?php
/**
 * unit-self-test:/Inspector.class.php
 *
 * @creation  2017-03-12
 * @version   1.0
 * @package   unit-self-test
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** Inspector
 *
 * @creation  2017-03-12
 * @version   1.0
 * @package   unit-self-test
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Inspector
{
	/** trait
	 *
	 */
	use OP_CORE;

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

//	d('current', $current);

		//	database
		foreach( ifset($inspection[$host][$prod][$port]['user'][$user]['databases'], []) as $db_name => $io ){
			//	...
			if( $io === false ){
				continue;
			}

			//	...
			$collation = $config['databases'][$db_name]['collation'] ?? null;

			//	table
			foreach( ifset($inspection[$host][$prod][$port]['user'][$user]['tables'][$db_name], []) as $table_name => $io ){
				//	...
				if( $io === false ){
					continue;
				}

				//	...
				$collation = $config['databases'][$db_name]['tables'][$table_name]['collation'] ?? $collation;

				//	...
				$table = self::_DifferenceStructAdjust(
					$config['databases'][$db_name]['tables'][$table_name]['fields'],
					$config['databases'][$db_name]['tables'][$table_name]['indexes'],
					$collation
				);

				//	field
				foreach( ifset($inspection[$host][$prod][$port]['user'][$user]['fields'][$db_name][$table_name], []) as $field_name => $io ){
					//	...
					if( $io === false ){
						continue;
					}

//d($db_name, $table_name, $field_name);

					//	...
					$inspection[$host][$prod][$port]['user'][$user]['struct'][$db_name][$table_name][$field_name] = self::_DifferenceStructResult(
						$table[$field_name],
						$current['databases'][$db_name]['tables'][$table_name]['fields'][$field_name]
					);
				}
			}
		}
	}

	/** Touch table's field struct by index.
	 *
	 */
	static function _DifferenceStructAdjust($table, $index, $collation)
	{
		//	auto increment
		if( $field_name = ifset($index['auto_increment']) ){
			if( empty($table[$field_name]['key']) ){
				$table[$field_name]['key'] = 'pri';
			}
			if( empty($table[$field_name]['null']) ){
				$table[$field_name]['null'] = false;
			}
			if( empty($table[$field_name]['type']) ){
				      $table[$field_name]['type'] = 'int';
			}
			if( empty($table[$field_name]['extra']) ){
				      $table[$field_name]['extra'] = 'auto_increment';
			}
		}

		//	primary key
		if( $field_name = ifset($index['primary']) ){
			if( empty($table[$field_name]['type']) ){
				$table[$field_name]['type'] = 'int';
			}
		}

		//	...
		foreach( $table as $field_name => $config ){
			//	type
			switch( $type = $config['type'] ){
				case 'int':
					if( empty($table[$field_name]['length']) ){
						$table[$field_name]['length'] = 11;
					}
					break;

				case 'text':
				case 'char':
				case 'varchar':
					if( empty($table[$field_name]['collation']) ){
							  $table[$field_name]['collation'] = $collation;
					}
					break;

				case 'timestamp':
					if( empty($table[$field_name]['null']) ){
						$table[$field_name]['default'] = 'current_timestamp';
						$table[$field_name]['extra']   = 'on update current_timestamp';
					}
					break;
			}

			//	null
			if(!isset($config['null'])){
				$table[$field_name]['null'] = true;
			}

			//	comment
			if(!isset($config['comment'])){
				$table[$field_name]['comment'] = '';
			}
		}

		return $table;
	}

	/** _DifferenceStructResult
	 *
	 * @param unknown $inspection
	 * @param unknown $config
	 * @param unknown $current
	 */
	static function _DifferenceStructResult($config, $current)
	{
		$result = [];

		//	...
		foreach( $current as $key => $val ){
			if( $key === 'field' or $key === 'privileges' ){
				continue;
			}

			//	...
			$value = ifset($config[$key]);

			//	...
			$result[$key] = $value === $current[$key] ? true: false;

			if( $result[$key] === false ){
				d($key, $value, $current[$key]);
			}
		}

		if( array_search(false, $result) ){
			d($current, $config);
		}

		return $result;
	}

	/** Inspect
	 *
	 * @param  array $configs
	 * @return array
	 */
	static function Inspect($configs)
	{
		//	...
		if( empty($configs) ){
			Html::E("Empty argument. (database configuration)");
			return false;
		}

		//	...
		$current    = [];
		$inspection = [];

		//	...
		foreach( $configs as $config ){
			//	...
			if(!$db = self::_init_db($config) ){
				return;
			}

			//	...
			if(!$sql = self::_init_sql($db) ){
				return;
			}

			//	...
			if( UnitSelfTest\Current::Get($current, $config, $db, $sql) ){
				//	...
				self::_Difference($inspection, $config, $current);
			}
		}

		//	...
		return $inspection;
	}
}