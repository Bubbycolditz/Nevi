<?php

    namespace Bubbycolditz\Nevi;

    class Database {

        private $pdo;

        public function __construct() {

            global $host, $dbName, $username, $password;

            try {

                $this->pdo = new PDO("mysql:host=$host;dbname=$dbName", $username, $password);

            } catch (PDOException $e) {

                die("Could not connect to the database. Please check your configuration. The following error has occured:<br><br>$e");

            }
        }

        /**

         * Creates a MySQL query with the given expression

         * @param string $table The table wanting to select from.
         * @param string $selector The value(s) to grab from the table.
         * @param string $expression The expression statement to grab all of the rows.
         * @return array The output of the function that satisfies the given expression.

         */
        public function pdoQuery($table, $selector, $expression){

            $stmt = $this->pdo->prepare("SELECT $selector FROM $table WHERE $expression");
            $stmt->execute();
            return $stmt->fetch();

        }

        /**

         * Creates a MySQL query with the given expression returning an array

         * @param string $table The table wanting to select from.
         * @param string $selector The value(s) to grab from the table.
         * @param string $expression The expression statement to grab all of the rows.
         * @param string $indexVariable The index variable to store in the array.
         * @param string $indexAssign The value attached to the indexVariable.
         * @return array The output from the array.

         */
        public function pdoArrayQuery($table, $selector, $expression, $indexVariable, $indexAssign){

            $array = [];

            $stmt = $this->pdo->prepare("SELECT $selector FROM $table WHERE $expression");
            $stmt->execute();
            $data = $stmt->fetchAll();

            foreach($data as $rows) {
                $array += array($rows[$indexVariable] => $rows[$indexAssign]);
            }

            return $array;

        }

        /**

         * Creates a MySQL query with the given expression returning an array

         * @param string $table The table wanting to fetch data from.
         * @param string $selector The value(s) to grab from the table.
         * @param string $expression The expression statement to grab all of the rows.
         * @param string $type The type of equation to be used. [**table**, **combo**]
         * @return array The output from the array.

         */
        public function pdoWhileQuery($table, $selector, $expression = false, $type = false){

            if($type == "table"){

                $stmt = $this->pdo->prepare("SELECT $selector FROM $table");

            } else {

                $stmt = $this->pdo->prepare("SELECT $selector FROM $table WHERE $expression");

            }

            $stmt->execute();
            return $stmt->fetchAll();

        }

        /**

         * Inserts data using a MySQL query

         * @param string $table The table wanting to select from.
         * @param array $columns The columns that want to be updated
         * @param array $values The values that want to be assigned to the columns
         * @return boolean

         */
        public function pdoInsertQuery($table, $columns, $values) {

            $cols = implode(',', $columns);
            $placeholders = implode(',', array_fill(0, count($columns), '?'));

            $stmt = $this->pdo->prepare("INSERT INTO $table ($cols) VALUES ($placeholders)");

            for($i = 0; $i < count($values); $i++) {

                $stmt->bindValue($i + 1, $values[$i]);

            }

            return $stmt->execute();
        }

        /**

         * Selects the total MySQL Database values with the given expression

         * @param string $table The table wanting to select from.
         * @param string $selector The value(s) to grab from the table.
         * @param string $expression The expression statement to grab all of the rows.
         * @return int The total rows that satisfies the given expression.

         */
        public function pdoNumRows($table, $selector, $expression){

            $stmt = $this->pdo->prepare("SELECT $selector FROM $table WHERE $expression");
            $stmt->execute();
            return $stmt->rowCount();

        }

        /**

         * Updates data using a MySQL query with the given expression

         * @param string $table The table wanting to select from.
         * @param array $columns The columns that want to be updated
         * @param array $values The values that want to be assigned to the columns
         * @param string $expression The expression statement to update with the given columns and table.
         * @return boolean

         */
        public function pdoUpdate($table, $columns, $values, $expression){

            $assignments = [];

            for($i = 0; $i < count($columns); $i++){

                $assignments[] = $columns[$i] . '=?';

            }

            $assignments = implode(',', $assignments);

            $stmt = $this->pdo->prepare("UPDATE $table SET $assignments WHERE $expression");

            for($i = 0; $i < count($values); $i++){

                $stmt->bindValue($i + 1, $values[$i]);

            }

            return $stmt->execute();

        }

        /**

         * Deletes data using a MySQL query with the given expression

         * @param string $table The table wanting to delete.
         * @param string $expression The expression statement to delete with the given table.
         * @return boolean

         */
        public function pdoDelete($table, $expression){

            $stmt = $this->pdo->prepare("DELETE FROM $table WHERE $expression");
            return $stmt->execute();

        }
    }