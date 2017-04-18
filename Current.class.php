<?php
/**
 * unit-self-test:/Current.class.php
 *
 * @creation  2017-04-11
 * @version   1.0
 * @package   unit-self-test
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */

/** use namespace.
 *
 */
namespace UnitSelfTest;

/** Current
 *
 * @creation  2017-04-11
 * @version   1.0
 * @package   unit-self-test
 * @author    Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright Tomoaki Nagahara All right reserved.
 */
class Current
{
	/** trait
	 *
	 */
	use \OP_CORE;

	/** Get current database setting.
	 *
	 * @param  array $configs
	 * @return array
	 */
	static function Get(&$result, $config, $db, $sql)
	{
		//	...
		$_db    = null;
		$_table = null;
		$_field = null;

		//	...
		$prod = $config['driver'];
		$host = $config['host'];
		$port = $config['port'];
		$user = $config['user'];

		//	...
		$result[$host][$prod][$port]['users'][$user] = $db ? true: false;

		//	...
		if( !$db ){
			return;
		}

		//	Get database name list.
		foreach( $db->Query($sql->Show()) as $temp ){
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
						switch( $key = lcfirst($key) ){
							case 'type':
								//	unsigned
								if( strpos($val, 'unsigned') ){
									$_field['unsigned'] = true;
								}

								//	length
								if( $st = strpos($val, '(') ){
									$en = strpos($val, ')');
									$length = substr($val, $st+1, $en-$st-1);
									$val    = substr($val, 0, $st);
									$_field['length'] = (int)$length;
								}
								break;

							// null is permit null.
							case 'null':
								$val = $val === 'YES' ? true: false;
								break;

							// key is index.
							case 'key':
								$val = empty($val) ? null: strtolower($val);
								break;

							//	extra is extra setting.
							case 'extra':
								$val = empty($val) ? null: strtolower($val);
								break;

							//	default is default value.
							case 'default':
								$val = empty($val) ? null: strtolower($val);
								break;

							// collation is near charset.
							case 'collation':
								if( empty($val) ){
									continue;
								}
								break;
						}

						//	...
						$_field[$key] = $val;
					}
				}
			}
		}
	}
}
