<?php
namespace euu_shippingintegration;

if (!defined('_PS_VERSION_')) exit;

use \Db;

class DbModel extends \ObjectModel {

	const TYPE_TEXT = 'text';
	const TYPE_MEDIUMTEXT = 'mediumtext';
	//todo : toggleStatus()
	//todo : add enum
	//todo : add default value
	//todo : instance check table and schema
	//todo : add force drop
	private static function _getTableName() {
		return (isset(static::$_PREFIX) && static::$_PREFIX == TRUE ? _DB_PREFIX_ . static::$definition['table'] : static::$definition['table']);
	}

	private static function _typeToSql($type) {
		switch ($type) {
			case static::TYPE_INT:
				return 'int';
			case static::TYPE_STRING:
				return 'varchar';
			case static::TYPE_FLOAT:
				return 'float';
			case static::TYPE_DATE:
				return 'datetime';
			case static::TYPE_BOOL:
				return 'tinyint(1)';
			case static::TYPE_TEXT;
				return 'text';
			case static::TYPE_MEDIUMTEXT;
				return 'mediumtext';
		}
	}

	private static function _makeColSqlFromDefination() {
		$col_sql = '';
		foreach (static::$definition['fields'] as $col_name => $col_info) {
			$col_sql .= "`{$col_name}` " .
						static::_typeToSql($col_info['type']) .
						(isset($col_info['size']) ? ($col_info['type'] == static::TYPE_BOOL ? '' : "({$col_info['size']})") : '') .
						(isset($col_info['required']) && $col_info['required'] == TRUE ? ' NOT NULL ' : '') .
						(isset($col_info['auto_date']) ? ($col_info['auto_date'] == 'insert' ? ' DEFAULT CURRENT_TIMESTAMP ' : ' ON UPDATE CURRENT_TIMESTAMP ') : '') .
						',';
		}

		return $col_sql;
	}

	private static function _makecolIndexFromDefination() {
		$index_sql = [];
		foreach (static::$definition['fields'] as $col_name => $col_info) {
			if (isset($col_info['uniq']) && $col_info['uniq'] == TRUE) {
				$index_sql[] = ",UNIQUE (`{$col_name}`)";
			}
		}
		$index_sql = implode('', $index_sql);

		return $index_sql;
	}

	public static function createTable() {
		$tableName = static::$definition['table'];
		$primaryField = static::$definition['primary'];
		$sql = '  			CREATE TABLE IF NOT EXISTS `' . (isset(static::$_PREFIX) && static::$_PREFIX == TRUE ? _DB_PREFIX_ . $tableName : $tableName) . '` (
  				`' . $primaryField . '` int(10) unsigned NOT NULL AUTO_INCREMENT,
  				' . static::_makeColSqlFromDefination() . '				
  				PRIMARY KEY (`' . $primaryField . '`)  				
  				' . static::_makecolIndexFromDefination() . ' 			
  				) ENGINE=InnoDB DEFAULT CHARSET=utf8;  		';

		return Db::getInstance()
				 ->execute($sql);
	}

	private function _reformatFields() {
		$autodate_fields = [];
		foreach ($this->def['fields'] as $field_name => $field_param) {
			if (isset($field_param['auto_date'])) {
				$autodate_fields[] = $field_name;
			}
		}
		$fields = $this->getFields();
		array_walk(
			$fields,
			function ($field_value, $field_name) use (&$fields, &$autodate_fields) {
				if (in_array($field_name, $autodate_fields)) {
					unset($fields[$field_name]);
				}
			}
		);

		return $fields;
	}

	public function insert($null_values = TRUE, $force_id = FALSE, $insert_type = DB::INSERT) {
		if (isset($this->id) && $force_id === FALSE) {
			unset($this->id);
		}
		$fields = $this->_reformatFields();
		if (!$result = Db::getInstance()
						 ->insert(
							 $this->def['table'],
							 $fields,
							 $null_values,
							 FALSE,
							 $insert_type,
							 static::$_PREFIX
						 )) {
			return FALSE;
		}
		// Get object id in database
		$this->id = Db::getInstance()
					  ->Insert_ID();

		return $result;
	}

	public function update($null_values = TRUE) {
		$fields = $this->_reformatFields();
		if (!$result = Db::getInstance()
						 ->update(
							 $this->def['table'],
							 $fields,
							 '`' . pSQL($this->def['primary']) . '` = ' . (int)$this->id,
							 0,
							 $null_values,
							 FALSE,
							 Db::INSERT,
							 static::$_PREFIX
						 )) {
			return FALSE;
		}

		return $result;
	}

	public function delete() {
		if (!$result = Db::getInstance()
						 ->delete(
							 $this->def['table'],
							 '`' . pSQL($this->def['primary']) . '` = ' . (int)$this->id,
							 0,
							 FALSE,
							 static::$_PREFIX
						 )) {
			return FALSE;
		}

		return $result;
	}

	// todo name change
	public static function selectInit($where, $order_by = NULL): self {
		$id = Db::getInstance()
				->getValue(
					'SELECT ' .
					static::$definition['primary'] .
					' FROM ' .
					static::_getTableName() .
					($where != NULL ? " WHERE {$where}" : '') .
					($order_by != NULL ? ' ORDER BY ' . implode(
							',',
							array_map(
								function ($column) {
									return implode(' ', $column);
								},
								$order_by
							)
						) : '')
				);

		return new static($id);
	}

	public static function selectAll(string $where = NULL, array $select_columns = NULL, array $order_by = NULL) {
		return (Db::getInstance()
				  ->executeS(
					  'SELECT '.($select_columns ? '`'.implode('`,`',$select_columns).'`' : '*').' FROM ' . static::_getTableName() . ($where != NULL ? " WHERE {$where}" : '') . ($order_by != NULL ? ' ORDER BY ' . implode(
							  ',',
							  array_map(
								  function ($column) {
									  return implode(' ', $column);
								  },
								  $order_by
							  )
						  ) : '')
				  ));
	}

	//todo : select columns active
	public static function selectRow(string $where = NULL, array $select_columns = NULL, array $order_by = NULL) {
		return (Db::getInstance()
				  ->getRow(
					  'SELECT '.($select_columns ? '`'.implode('`,`',$select_columns).'`' : '*').' FROM ' . static::_getTableName() . ($where != NULL ? " WHERE {$where}" : '') . ($order_by != NULL ? ' ORDER BY ' . implode(
							  ',',
							  array_map(
								  function ($column) {
									  return implode(' ', $column);
								  },
								  $order_by
							  )
						  ) : '')
				  ));
	}

	public static function selectValue(string $select_column, string $where = '1', array $order_by = NULL) {
		return (Db::getInstance()
				  ->getValue(
					  'SELECT ' . $select_column . ' FROM ' . static::_getTableName() . " WHERE {$where} " . ($order_by != NULL ? ' ORDER BY ' . implode(
							  ',',
							  array_map(
								  function ($column) {
									  return implode(' ', $column);
								  },
								  $order_by
							  )
						  ) : '')
				  ));
	}

	//note is
	public static function check(string $where) {
		return (Db::getInstance()
				  ->getValue('SELECT COUNT(*) FROM ' . static::_getTableName() . " WHERE {$where}"));
	}

	public static function deleteRow(string $where) {
		return (Db::getInstance()
				  ->delete(static::$definition['table'], $where, 0, FALSE, static::$_PREFIX));
	}

	public static function insertRow(array $fields, $insert_type = Db::INSERT) {
		return (Db::getInstance()
				  ->insert(
					  static::$definition['table'],
					  $fields,
					  TRUE,
					  FALSE,
					  $insert_type,
					  static::$_PREFIX
				  ));
	}

	public static function updateRow(array $fields, string $where) {
		return (Db::getInstance()
				  ->update(
					  static::$definition['table'],
					  $fields,
					  $where,
					  0,
					  TRUE,
					  FALSE,
					  static::$_PREFIX
				  ));
	}

}