<?php
	/**
	 * Class: Query
	 * Handles a query based on the <SQL.interface>.
	 */
	class Query {
		# Variable: $query
		# Holds the current query.
		public $query = "";

		/**
		 * Function: __construct
		 * Creates a query based on the <SQL.interface>.
		 *
		 * Parameters:
		 *     $query - Query to execute.
		 *     $params - An associative array of parameters used in the query.
		 */
		public function __construct($query, $params = array()) {
			$this->db =& SQL::current()->db;
			$this->interface =& SQL::current()->interface;

			switch($this->interface) {
				case "pdo":
					try {
						$this->query = $this->db->prepare($query);
						$result = $this->query->execute($params);
						$this->query->setFetchMode(PDO::FETCH_ASSOC);
						if (!$result) throw new PDOException;
					} catch (PDOException $error) {
						$message = preg_replace("/SQLSTATE\[.*?\]: .+ [0-9]+ (.*?)/", "\\1", $error->getMessage());

						if (XML_RPC or $throw_exceptions)
							throw new Exception($message);

						if (DEBUG)
							$message.= "\n\n".$query."\n\n<pre>".print_r($params, true)."</pre>\n\n<pre>".$error->getTraceAsString()."</pre>";

						$this->db = null;

						error(__("Database Error"), $message);
					}
					break;
				case "mysqli":
					foreach ($params as $name => $val)
						$query = str_replace($name, "'".$this->db->escape_string($val)."'", $query);
					$this->query = $this->db->query($query);
					break;
				case "mysql":
					foreach ($params as $name => $val)
						$query = str_replace($name, "'".@mysql_real_escape_string($val)."'", $query);
					$this->query = @mysql_query($query);
					break;
			}
		}

		/**
		 * Function: fetchColumn
		 * Fetches a column of the first row.
		 *
		 * Parameters:
		 *     $column - The offset of the column to grab. Default 0.
		 */
		public function fetchColumn($column = 0) {
			switch($this->interface) {
				case "pdo":
					return $this->query->fetchColumn($column);
				case "mysqli":
					$result = $this->query->fetch_array();
					return $result[$column];
				case "mysql":
					$result = @mysql_fetch_array($this->query);
					return $result[$column];
			}
		}

		/**
		 * Function: fetch
		 * Returns the first row as an array.
		 */
		public function fetch() {
			switch($this->interface) {
				case "pdo":
					return $this->query->fetch();
				case "mysqli":
					return $this->query->fetch_array();
				case "mysql":
					return @mysql_fetch_array($this->query);
			}
		}

		/**
		 * Function: fetchObject
		 * Returns the first row as an object.
		 */
		public function fetchObject() {
			switch($this->interface) {
				case "pdo":
					return $this->query->fetchObject();
				case "mysqli":
					return $this->query->fetch_object();
				case "mysql":
					return @mysql_fetch_object($this->query);
			}
		}

		/**
		 * Function: fetchAll
		 * Returns an array of every result.
		 */
		public function fetchAll($style = null) {
			switch($this->interface) {
				case "pdo":
					return $this->query->fetchAll($style);
				case "mysqli":
					$results = array();
					while ($row = $this->query->fetch_assoc())
						$results[] = $row;
					return $results;
				case "mysql":
					$results = array();
					while ($row = @mysql_fetch_assoc($this->query))
						$results[] = $row;
					return $results;
			}
		}
	}