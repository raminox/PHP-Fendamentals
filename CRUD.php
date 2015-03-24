<?php
class CRUD extends PDO {
	public $table;
	
	/**
	 * Start connection to database
	 *
	 * @param string $db_type        	
	 * @param string $db_host        	
	 * @param string $db_name        	
	 * @param string $db_user        	
	 * @param string $db_pass        	
	 * @return boolean
	 */
	public function __construct($db_type, $db_host, $db_name, $db_user, $db_pass) {
		$this->db_type = $db_type;
		$this->db_host = $db_host;
		$this->db_name = $db_name;
		$this->db_user = $db_user;
		$this->db_pass = $db_pass;
		
		$dsn = "$this->db_type:host=$this->db_host;dbname=$this->db_name";
		
		try {
			parent::__construct ( $dsn, $this->db_user, $this->db_pass );
			$this->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			$this->setAttribute ( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
		} catch ( PDOException $e ) {
			echo 'unable to connect to database errors :' . $e->getMessage ();
			return false;
			die ();
		}
	}
	
	/**
	 * Insert method
	 *
	 * @param
	 *        	array key/value $items
	 * @return string|boolean
	 */
	public function insert($items) {
		$table = $this->getTable ();
		
		$keys = array_keys ( $items );
		$col_name = '`' . implode ( '`,`', $keys ) . '`';
		$bind_values = ':' . implode ( ',:', $keys );
		
		$stmt = $this->prepare ( "INSERT INTO `$table` ($col_name) VALUES($bind_values)" );
		$insert = $stmt->execute ( $items );
		if ($insert) {
			return $this->lastInsertId ();
		} else {
			return false;
		}
	}
	
	/**
	 * Select method
	 *
	 * @param array/'*' $items        	
	 * @param int/array/null $condition        	
	 * @return mixed
	 */
	public function select($items, $condition = null) {
		$table = $this->getTable ();
		
		if (is_array ( $condition )) {
			$bindValues = $condition;
		} else {
			$bindValues = '';
		}
		
		$condition = $this->condition ( $condition );
		
		if (is_array ( $items )) {
			$items = '`' . implode ( '`,`', $items ) . '`';
		}
		
		$stmt = $this->prepare ( "SELECT $items FROM `$table` $condition" );
		$stmt->execute ( $bindValues );
		
		return $stmt->fetchAll ();
	}
	
	/**
	 * Update method
	 *
	 * @param array $items        	
	 * @param int/array $condition        	
	 */
	public function update($items, $condition) {
		$table = $this->getTable ();
		
		if (is_array ( $condition )) {
			$bind_values = array_merge ( $item, $condition );
		}
		// Working on the set part of the SQL request
		$set = '';
		
		foreach ( $items as $key => $item ) {
			$set .= "`$key`" . '=' . ":" . "$key" . ", ";
		}
		// Remove the last space & comma from the request line
		$set = rtrim ( $set, ', ' );
		
		$condition = $this->condition ( $condition );
		
		$stmt = $this->prepare ( "UPDATE `$table` SET $set $condition" );
		$stmt->execute ( $bind_values );
	}
	
	/**
	 * Delete moethod
	 *
	 * @param int/array $condition        	
	 */
	public function delete($condition) {
		$table = $this->getTable ();
		
		if (is_array ( $$condition )) {
			$bind_values = $condition;
		}
		
		$condition = $this->condition ( $condition );
		$stmt = $this->prepare ( "DELETE FROM `$table` $condition" );
		$stmt->execute ( $bind_values );
	}
	
	/**
	 * Set the table to use
	 *
	 * @param string $table        	
	 * @throws Exception
	 */
	public function setTable($table) {
		if (empty ( $table )) {
			throw new Exception ( 'The table name must not be null' );
		} else {
			$this->table = $table;
		}
	}
	
	/**
	 * Get the table to use
	 *
	 * @return string
	 */
	protected function getTable() {
		return $this->table;
	}
	
	/**
	 * Handle condition "Where" in this calss
	 *
	 * @param int/array $condition        	
	 */
	protected function condition($condition) {
		if ($condition == null) {
			
			return $this->condition = null;
		} elseif (is_numeric ( $condition )) {
			return $this->condition = "WHERE `id`=" . $condition;
		} elseif (is_array ( $condition )) {
			return $this->condition = "WHERE " . '`' . implode ( array_keys ( $condition ) ) . '`=:' . implode ( array_keys ( $condition ) );
		}
	}
}


