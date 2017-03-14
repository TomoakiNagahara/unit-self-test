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
	static function Inspection($db, $sql)
	{
		//	...
		$result = [];

		//	Get database name list.
		foreach( $db->Query($sql->Show()) as $temp ){
			$db_name = $temp['Database'];
			if( $db_name === 'information_schema' ){
				continue;
			}

			//	Get table name list.
			foreach( $db->Query($sql->Show(['database'=>$db_name])) as $temp1 ){
				$table_name = $temp1["Tables_in_{$db_name}"];

				//	Get Table index.
				$indexes = $db->Query($sql->Show(['database'=>$db_name, 'table'=>$table_name, 'index'=>1]));
				$result[$db_name][$table_name]['indexes'] = $indexes;

				//	Get table define.
				foreach( $db->Query($sql->Show(['database'=>$db_name, 'table'=>$table_name])) as $temp2 ){
					$field_name = $temp2['Field'];

					//	Set filed name and define.
					foreach( $temp2 as $key => $val ){
						$key = lcfirst($key);
						if( $key === 'type' ){
							//	unsigned
							if( strpos($val, 'unsigned') ){
								$result[$db_name][$table_name][$field_name]['unsigned'] = true;
							}
							//	length
							if( $st = strpos($val, '(') ){
								$en = strpos($val, ')');
								$length = substr($val, $st+1, $en-$st-1);
								$val    = substr($val, 0, $st);
								$result[$db_name][$table_name][$field_name]['length'] = $length;
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
						$result[$db_name][$table_name]['fileds'][$field_name][$key] = $val;
					}
				}
			}
		}

		//	...
		return $result;
	}
}