<?php
	/**
	 * Class: QueryBuilder
	 * A generic SQL query builder. All methods are static so there's no point in instantiating.
	 */
	class QueryBuilder {
		/**
		 * Function: build_update_values
		 * Creates an update data part.
		 */
		public static function build_update_values($data) {
			$set = array();

			foreach ($data as $field => $val)
				array_push($set, "`$field` = $val");

			return implode(", ", $set);
		}

		/**
		 * Function: build_insert_header
		 * Creates an insert header part.
		 */
		public static function build_insert_header($data) {
			$set = array();

			foreach (array_keys($data) as $field)
				array_push($set, "`$field`");

			return "(".implode(", ", $set).")";
		}

		/**
		 * Function: build_insert_values
		 * Creates an insert data part.
		 */
		public static function build_insert_values($data) {
			return "(".implode(', ', array_values($data)).")";
		}

		/**
		 * Function: build_insert
		 * Creates a full insert query.
		 */
		public static function build_insert($table, $data) {
			$sql = SQL::current();
			return "
				INSERT INTO `{$sql->prefix}$table`
				".self::build_insert_header($data)."
				VALUES
				".self::build_insert_values($data)."
			";
		}

		/**
		 * Function: build_update
		 * Creates a full update query.
		 */
		public static function build_update($table, $conds, $data) {
			$sql = SQL::current();
			return "
				UPDATE `{$sql->prefix}$table`
				SET ".self::build_update_values($data)."
				".($conds ? "WHERE $conds" : "")."
			";
		}

		/**
		 * Function: build_delete
		 * Creates a full delete query.
		 */
		public static function build_delete($table, $conds) {
			$sql = SQL::current();
			return "
				DELETE FROM `{$sql->prefix}$table`
				".($conds ? "WHERE $conds" : "")."
			";
		}

		/**
		 * Function: build_limits
		 * Creates a LIMIT part for a query.
		 */
		public static function build_limits($offset, $limit) {
			if ($limit === null)
				return "";
			if ($offset !== null)
				return "LIMIT $offset, $limit";
			return "LIMIT $limit";
		}

		/**
		 * Function: build_from
		 * Creates a FROM header for select queries.
		 * Table names are automatically prefixed.
		 */
		public static function build_from($tables) {
			if (!is_array($tables))
				$tables = array($tables);
			$set = array();
			$sql = SQL::current();
			foreach ($tables as $table) {
				$table = explode(" ", $table);
				$parts = explode(".", $table[0]);
				$parts[0] = $sql->prefix.$parts[0];
				foreach ($parts as & $part)
					$part = "`$part`";
				$table[0] = implode(".", $parts);
				array_push($set, implode(" ", $table));
			}
			return implode(", ", $set);
		}

		/**
		 * Function: build_count
		 * Creates a SELECT COUNT(1) query.
		 */
		public static function build_count($tables, $conds) {
			return "
				SELECT COUNT(1) AS count
				FROM ".self::build_from($tables)."
				".($conds ? "WHERE $conds" : "")."
			";
		}

		/**
		 * Function: build_select_header
		 * Creates a SELECT fields header.
		 */
		public static function build_select_header($fields) {
			if (!is_array($fields))
				$fields = array($fields);
			$set = array();

			foreach ($fields as $field) {
				$field = explode(" ", $field);
				$parts = explode(".", $field[0]);
				foreach ($parts as & $part)
					if ($part != '*')
						$part = "`$part`";
				$field[0] = implode(".", $parts);
				array_push($set, implode(" ", $field));
			}

			return implode(', ', $set);
		}

		/**
		 * Function: build_select
		 * Creates a full SELECT query.
		 */
		public static function build_select($tables, $fields, $conds, $order = null, $limit = null, $offset = null) {
			return "
				SELECT ".self::build_select_header($fields)."
				FROM ".self::build_from($tables)."
				".($conds ? "WHERE $conds" : "")."
				ORDER BY $order
				".self::build_limits($offset, $limit)."
			";
		}
	}