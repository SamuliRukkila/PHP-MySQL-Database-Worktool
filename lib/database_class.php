  <?php

// Samuli Rukkila
// l4924(at)student.jamk.fi


    /**
     * Database-class which contains all the necessary constructions and functions.
     * In here you can manipulate databases as well as include new tables and values
     * for columns.
     *
     * Object is being made automatically from this class in the main page.
     *
     * @var PRIVATE $connection - includes connection for MySQL
     * @var PRIVATE $server - server name (usually localhost)
     * @var PRIVATE $user - MySQL username
     * @var PRIVATE $password - MySQL password
     *
     * @author Samuli Rukkila <l4924student.jamk.fi>
     *
     */
    class Database_class {

      private $connection;
      private $server;
      private $user;
      private $password;

      /**
       * Class constructor.
       * Add values given by the main page to make an object.
       *
       * @param STRING $server - name of the server
       * @param STRING $user - MySQL username
       * @param STRING $password - MySQL password
       *
       * @var STRING $basketball, $chainstore, $steam, $library -
       *  Adding 'database_container.php' to the constructor to include four
       *  ready-made variables which can be used while making a new database.
       *
       */
      function __construct ($server, $user, $password) {
        $this->server = $server;
        $this->user = $user;
        $this->password = $password;

        include 'database_content.php';
          $this->basketball = $basketball;
          $this->chainstore = $chainstore;
          $this->steam = $steam;
          $this->library = $library;
      }


      /**
       * Connection function.
       *
       * This functions connects you to the MySQL server according to your MySQL
       * username and password. It'll also set 'utf-8' as a default charset.
       *
       * @throws EXCEPTION - function won't be able to connect to the server
       * @var    EXCEPTION $e - grabs the error message
       *
       */
      function connect () {

        try {
          $this->connection = mysqli_connect($this->server, $this->user, $this->password);
          mysqli_set_charset($this->connection,'utf8');
          if (!$this->connection) {
            throw new Exception('Cannot connect to the server!');
          }
        }
        catch (Exception $e) {
            die($e->getMessage());
        }
      }


      /**
       * Function which will toggle between safe-mode.
       *
       * Normally the safe-mode will be on. In this mode you'll be in a way -
       * unable - to use any MySQL databases. Only those in which you create
       * in the program will be avaible and they all start with a name "safe_mode_".
       *
       * Every necessary function will check if the safe mode is on or off and will
       * display the databases accordingly.
       *
       * If your safe-mode is on, and you try to change the safe-mode
       * to ON again, the function will detect that and do nothing.
       *
       */
      function safe_mode () {

        if (isset($_POST['mode']) && isset($_POST['change_error_status'])) {

          if ($_POST['mode'] == 'on' && !$_SESSION['safe_mode']) {
            session_unset();
            $_SESSION['safe_mode'] = true;
            header('Location: '.$_SERVER['PHP_SELF']);
          }
          elseif ($_POST['mode'] == 'off' && $_SESSION['safe_mode']) {
            session_unset();
            $_SESSION['safe_mode'] = false;
            header('Location: '.$_SERVER['PHP_SELF']);
          }
        }
      }


      /**
       * A function which will put the latest MySQL error to a session variable.
       */
      function printError () {

          $_SESSION['warning_message'] = mysqli_error($this->connection).' (
            <i>Error number: '.mysqli_errno($this->connection).'</i> )';
      }



      /**
       * Completely destroy the current session.
       */
      function reload () {

        if (isset($_POST['reload_page'])) {
          session_unset();
          header('Location: '.$_SERVER['PHP_SELF']);
        }
      }


      /**
       * Switches between showing MySQL errors and not showing them. User will
       * be able to toggle between those two. Trying to toggle to the same status
       * won't do anything.
       *
       */
      function errorStatus () {

        if (isset($_POST['change_status'])) {

          if ($_SESSION['enable_errors']) {
            $_SESSION['enable_errors'] = false;
          }
          else {
            $_SESSION['enable_errors'] = true;
          }
          header('Location: '.$_SERVER['PHP_SELF']);
        }
      }



      /**
       * Function which prints all the available databases.
       *
       * This function grabs all the databases available from the MySQl-server.
       * It will then print them one by one within the loop inside a form. All
       * of them will be included inside radio buttons so they can be chosen for
       * later modification. In case there's no databases found, user will be
       * notified.
       *
       * If the safe mode is on it'll only grab those databases containing the
       * characters "safe_mode_%" on them.
       *
       * @var STRING $db_search - include all databases (exluding information databases)
       * @var ARRAY $db - array which includes all of the database's names
       *
       * @var QUERY $db_query - grabs all databases to the variable
       *
       */
      function showDatabases () {

        // Exclude information databases..
        $db_search = "SHOW DATABASES WHERE `Database` NOT LIKE ('%schema%') AND
        `Database` NOT IN ('phpmyadmin', 'mysql', 'sys')";

        if ($_SESSION['safe_mode']) {
          $db_search .= " AND `Database` LIKE ('safe_mode_%')";
        }
        // ..and databases without the name "safe_mode_" if safe-mode is off
        else {
          $db_search .= " AND `Database` NOT LIKE ('safe_mode_%')";
        }

        $db_query = mysqli_query($this->connection, $db_search);

        if (!$db_query) {
          $this->printError();
        }
        else {
          if (mysqli_num_rows($db_query) > 0) {
            while ($db = mysqli_fetch_array($db_query))
            {
              if (isset($_SESSION['database']) && $db[0] == $_SESSION['database']) {
                // If the name is same with the current selected database auto-check it
                echo '<p><input type="radio" name="db" value="'.$db[0].'" checked>'.$db[0].'</p>';
              }
              else {
                echo '<p><input type="radio" name="db" value="'.$db[0].'">'.$db[0].'</p>';
              }
            }
          }
          else {
            echo '<p>No databases detected.</p>';
          }
        }
      }



      /**
       * Function where database is being chosen, deleted or created.
       *
       * Dependant on which submit button is being pressed.
       *
       * Choosing the database will put the name of the database to the session
       * variable. Any search queries open at that time will be unset.
       *
       *
       * In database deletion the function will try to do a delete-query where
       * it'll delete the selected database. In case it goes wrong an error
       * message will be shown.
       *
       * @var STRING $db - name of the database
       * @var STRING $query - contains a query to delete the database
       *
       *
       * When new database is being maden, name is being filtered for any malicous symbols.
       * Function then tries to create that new database - if it succeeds, the
       * new database is created. In the end the content
       * is being imported into the database.
       *
       * @var STRING $content_name - name of the wanted content
       * @var STRING $db_content - content which will be imported to database
       * @var STRING $db - database's name
       *
       * @var QUERY $db_query - query for new database creation
       * @var QUERY $multi_query - query to import content into database
       *
       */
      function modifyDatabase () {

        if (isset($_POST['choose_db']) || isset($_POST['delete_db'])) {
          if (!empty($_POST['db'])) {

          // CHOOSE
            if (isset($_POST['choose_db']) && !empty($_POST['db'])) {
              if (isset($_SESSION['search'])) {
                   unset($_SESSION['search']);
              }
              $_SESSION['database'] = $_POST['db'];
            }

          // DELETE
            else {
              $db = $_POST['db'];
              $query = "DROP DATABASE IF EXISTS `$db`;";

              // If you were modifying same database
              if ($_SESSION['database'] == $db) {
                unset($_SESSION['database']);
                unset($_SESSION['search']);
              }
              if (!mysqli_query($this->connection, $query)) {
                $this->printError();
              }
            }
            header('Location: '.$_SERVER['PHP_SELF']);
          }
        }


      // CREATE
        elseif (isset($_POST['create_db']) && !empty($_POST['db'])
          && !empty($_POST['content'])) {

          $content_name = $_POST['content'];
          $db_content = $this->$content_name;

          $db = mysqli_real_escape_string($this->connection, $_POST['db']);

          if ($_SESSION['safe_mode']) {
            $db = 'safe_mode_'.$db;
          }

          $db_query = "CREATE DATABASE IF NOT EXISTS `$db`;";
          mysqli_query($this->connection, $db_query);

          if ($_POST['content'] != 'empty') {
            mysqli_select_db($this->connection, $db);
            $multi_query = mysqli_multi_query($this->connection, $db_content);
          }

          if (!$db_query || !$multi_query) {
            $this->printError();
          }
          header('Location: '.$_SERVER['PHP_SELF']);
        }
      }



      /**
       * Function to hide the current database.
       *
       * This function will be enabled when database has been chosen. Dismissing
       * the database will remove the whole session (database and search (if
       * enabled) session variables).
       *
       */
      function hideDatabase () {

        if (isset($_POST['hide_db'])) {

          unset($_SESSION['search']);
          unset($_SESSION['database']);
          header('Location: '.$_SERVER['PHP_SELF']);
        }
      }


      /**
       * Function to remove rows.
       *
       * This function removes wanted row by user. If it's impossible to remove
       * that spefic row, user will be notified. Function includes parameters to
       * fully identify the wanted row.
       *
       * @param STRING $keyword - keyword which identifies the row
       * @param STRING $column - column which identifies the row
       * @param STRING $table - table, in which the row rests
       *
       * @var STRING $query - row deletion query
       *
       */
      function removeRow ($keyword, $column, $table) {

        /* Define POST-variable with 2 variables put together so the removation is unique */
        if (isset($_POST[$keyword.$table])) {

          mysqli_select_db($this->connection, $_SESSION['database']);
          $query = "DELETE FROM `$table` WHERE `$column` = '$keyword';";

          if (!mysqli_query($this->connection, $query)) {
            $this->printError();
          }
          header('Location: '.$_SERVER['PHP_SELF']);
        }
      }


      /**
       * Function which deletes columns.
       *
       * This function will try to delete the wanted column from a specific
       * table. It'll take parametres in order to remove only the wanted column.
       * When the function knows the specific table and column, it'll try to
       * remove it via a query. If it fails, an error message will pop up to
       * notify the user.
       *
       * @param STRING $column_key - contains the first column in table for speficiation
       * @param STRING $table - contains the table where the removal is being happening
       *
       * @var STRING $column - the column which the user wants to remove
       * @var QUERY $query - a query which will try to remove the column
       *
       */
      function removeColumn ($column_key, $table) {

        // Define POST-variable with 2 variables put
        // together so the removation is unique
        if (isset($_POST[$column_key.$table])) {

          mysqli_select_db($this->connection, $_SESSION['database']);
          $column = $_POST[$table.$table];

          $query = "ALTER TABLE `$table` DROP `$column`;";

          if (!mysqli_query($this->connection, $query)) {
            $this->printError();
          }
          header('Location: '.$_SERVER['PHP_SELF']);
        }
      }



      /**
       * Function which deletes table.
       *
       * This function will try to delete the table user wants. If the deletion
       * is unsuccessful, user will be notified with the error message.
       *
       * @param STRING $table - specified table name
       * @var MYSQL_QUERY $query - SQL-query for deleting table
       *
       */
      function removeTable ($table) {

        if (isset($_POST[$table])) {

          mysqli_select_db($this->connection, $_SESSION['database']);
          $query = "DROP TABLE IF EXISTS `$table`;";

          if (!mysqli_query($this->connection, $query)) {
            $this->printError();
          }
          header('Location: '.$_SERVER['PHP_SELF']);
        }
      }


      /**
       * Function where new rows are added to table.
       *
       * In here you'll be able to insert full rows inside a table. The amount of
       * values needed to create a full row are given from $number -parameter. If
       * there're are value spots left blank, it will print "NULL" to that part instead
       * leaving it empty when query is executed. Through loop a complete query
       * is made.
       *
       * Isset-statement contains a string with a table's name so the row addition
       * is unique.
       *
       * @param STRING $table - name of the table where rows are being inserted
       * @param INT $number - amount of value spots inside a table
       *
       * @var STRING $query - contains complete query to be executed
       * @var STRING $field - 1 value spot from whole row
       *
       */
      function addRow ($table, $number) {

        if (isset($_POST['add_row_'.$table])) {

          mysqli_select_db($this->connection, $_SESSION['database']);
          $query = "INSERT INTO `$table` VALUES ";
          $query .= "(";

          for ($i=0; $i < $number; $i++) {
            $field = mysqli_real_escape_string($this->connection, $_POST[$i.'_field_'.$table]);
            if (empty($field)) {
              $query .= "NULL,";
            }
            else {
              $query .= "'$field',";
            }
          }

          // Remove last comma from loop string
          $query = substr($query, 0, -1);
          $query .= ");";
          $_SESSION['query'] = $query.'|||||'.$number;
          if (!mysqli_query($this->connection, $query)) {
            $this->printError();
          }
          header('Location: '.$_SERVER['PHP_SELF']);
        }
      }



      /**
       * Function where you'll be able to add columns to existing table.
       *
       * This function will take all the POST-variables needed to it's own
       * local variables. From that information it'll try to construct a working
       * query executed to MySQL. In case that column is being wanted with "NOT
       * NULL" parameter. It'll be then added to the end of the query.
       *
       * @param STRING $table - name of the table
       *
       * @var STRING $column - name of the column being created
       * @var STRING $column_dt - datatype of that column
       * @var STRING $query - a query to be executed
       *
       */
      function addColumn ($table) {

        if (isset($_POST['add_column'.$table]) && !empty($_POST['column_name'])
          && !empty($_POST['column_dt'])) {

          mysqli_select_db($this->connection, $_SESSION['database']);

          $column = mysqli_real_escape_string($this->connection, $_POST['column_name']);
          $column_dt = mysqli_real_escape_string($this->connection, $_POST['column_dt']);
          $query = "ALTER TABLE  `$table` ADD `$column` $column_dt ";

          if (isset($_POST['not_null'])) {
            $query .= "NOT NULL";
          }

          if (!mysqli_query($this->connection, $query)) {
            $this->printError();
          }
          header('Location: '.$_SERVER['PHP_SELF']);
        }
      }



      /**
       * Function to add table for existing database.
       *
       * This function takes several inputs from user. Namely table's name,
       * key column and data type + all additional information wanted. Function
       * will then build a working SQL-query from those information bits. The
       * function will also check if some columns will have "NOT NULL" specifications.
       *
       * If user's query to make table unsucceeds, the error message will be sent
       * to a session variable suitable for the situtation. It'll be then shown
       * to the user in the main page.
       *
       * @var STRING $table - name of the created table
       * @var STRING $key - key column of the table
       * @var STRING $key_dt - data type of the key column
       * @var STRING $column - any voluntary columns for the table
       * @var STRING $column_dt - voluntary columns data types
       *
       * @var STRING $query - working SQL-query from the information which will
       *  create the table and all content inside of it
       *
       */
      function addTable () {

        if (isset($_POST['create_table']) && !empty($_POST['table']) &&
         !empty($_POST['key']) && !empty($_POST['key_dt'])) {

          // If search is in set, unset it to show the changes
          if (isset($_SESSION['search'])) {
            unset($_SESSION['search']);
          }

          mysqli_select_db($this->connection, $_SESSION['database']);

          $table = mysqli_real_escape_string($this->connection, $_POST['table']);
          $key = mysqli_real_escape_string($this->connection, $_POST['key']);
          $key_dt = mysqli_real_escape_string($this->connection, $_POST['key_dt']);

          // If user wants AUTO_INCREMENT to primary key
          if (isset($_POST['key_auto'])) {
            $query = "(`$key` $key_dt PRIMARY KEY AUTO_INCREMENT,";
          }
          else {
            $query = "(`$key` $key_dt PRIMARY KEY,";
          }

          // If any voluntary columns are being given.
          for ($i=2; $i < 8; $i++) {
            if (!empty($_POST['column'.$i])) {
              if (!empty($_POST['column_dt'.$i])) {

                $column = mysqli_real_escape_string($this->connection,
                 $_POST['column'.$i]);
                $column_dt = mysqli_real_escape_string($this->connection,
                 $_POST['column_dt'.$i]);

                // If user wants NOT NULL-modifier
                if (isset($_POST['column_notnull'.$i])) {
                  $column_dt .= " NOT NULL";
                }
                $query .= " `$column` $column_dt,";
              }
            }
          }

          // If user has given any additional SQL code
          if (!empty($_POST['additional_code'])) {
            $query .= mysqli_real_escape_string($this->connection,
              $_POST['additional_code']);
          }
          else {
            // Remove the last comma from the query if any additional SQL has not been given
            $query = substr($query, 0, -1);
          }

          $query .= ") ENGINE=INNODB;";
          $query = "CREATE TABLE IF NOT EXISTS `$table` $query";

          if (!mysqli_query($this->connection, $query)) {
              $_SESSION['table_warning'] = '<b>Error!</b> '.
                mysqli_error($this->connection).' ('.mysqli_errno($this->connection).
                ') <br><br><div class="sql_code">'.$query.'</div><br><br>';
          }
          header('Location: '.$_SERVER['PHP_SELF']);
        }
      }



      /**
       * Function which confirms that user has notified the error.
       *
       * In case there will be an error while creating a table for database, user
       * will notified by it in the main page. When users clicks the "Got it!"
       * button, this function will be prompted. It'll confirm that the button has
       * been pressed and it will remove the warning from the user.
       *
       */
      function confirmError () {

        if (isset($_POST['confirm_error_table']))
        {
          unset($_SESSION['table_warning']);
          header('Location: '.$_SERVER['PHP_SELF']);
        }
        elseif (isset($_POST['confirm_error']))
        {
          unset($_SESSION['warning_message']);
          header('Location: '.$_SERVER['PHP_SELF']);
        }
      }




      /**
       * Function to print the whole database.
       *
       * After the session variable ($database) has been established this function
       * will print the name of the database. Afterwards it'll also print the whole
       * concept of the database inside a <table> including tables, columns, rows and
       * values. It'll also print the possibility to remove tables/rows and to do
       * search functions.
       *
       * @var MYSQL_QUERY $table_query - query to find all of the tables inside database
       * @var MYSQL_QUERY $column_query - query to find all of the columns inside table
       *
       * @var ARRAY $table - includes all the names of the tables
       * @var ARRAY $column - includes all the names of the columns
       * @var ARRAY $row - includes all the values of the rows
       * @var STRING $value - temporary variable to store row's values
       *
       * @var ARRAY $column_array - temporary array to store columns, if deleted later
       * @var ARRAY $row_array - temporary array to store rows, if deleted later
       *
       * @var MYSQL-QUERY $query - query to import rows and values from column
       *
       */
      function printDatabase () {

        echo '<h2>'.$_SESSION['database'].'</h2><hr>';

        mysqli_select_db($this->connection, $_SESSION['database']);
        $table_query = mysqli_query($this->connection, "SHOW TABLES;");

        // Atleast 1 table has to be found
        if (mysqli_num_rows($table_query) > 0) {
          while ($table = mysqli_fetch_array($table_query))
          {
            // Include key column's all values inside an array
            $id_value_array = [];

            // Echoes a form where you'll be able to remove tables
            echo '<table id="empty_table"><tr>
                  <td><form action="'.$this->removeTable($table[0]).'" method="post">
                  <input class="red remove" type="submit" value="-" name="'.$table[0].'">
                  <td><h3>'.$table[0].'</h3></td></tr></form></table>';

            $column_query = mysqli_query($this->connection, "SHOW COLUMNS FROM `$table[0]`;");

              // Save columns into array for further deletion
              $column_array = [];

              while ($column = mysqli_fetch_array($column_query)) {
                array_push($column_array, $column[0]);
              }
              echo '<table class="db_table"><tr>';

              // The longest word in a row will be saved to this array, with it
              // we can determine the exact width needed for input text-boxes
              $row_length_array = [];

              // This will actually print the columns saved to an array
              foreach ($column_array as $column)
              {
                  echo '<th>'.$column.'</th>';
                  array_push($row_length_array, $column);
              }
              echo '</tr>';
              $query = mysqli_query($this->connection, "SELECT * FROM `$table[0]`;");

              while ($row = mysqli_fetch_assoc($query)) {

                echo '<tr>';

                // Save rows into array for further deletion
                $row_array = [];

                $a = 0;
                foreach ($row as $value) {
                  echo '<td>'.$value.'</td>';
                  array_push($row_array, $value);

                  if ($a == 0) {
                    array_push($id_value_array, $value);
                  }

                  // If the word in a row is longer than previous one, we'll
                  // save it to the array used while determining textboxes width's
                  if (strlen($value) > strlen($row_length_array[$a])) {
                    $row_length_array[$a] = $value;
                  }
                  $a++;
                }

                // Echoes a form where you'll be able to remove rows
                echo '<td id="empty_table"><form action="'.$this->removeRow($row_array[0],
                      $column_array[0], $table[0]).'" method="post">
                      <input class="red remove" type="submit" value="-"
                      name="'.$row_array[0].$table[0].'"></form>
                      </td></tr>';
              }

              echo '<tr><form action"'.$this->addRow($table[0], count($column_array)).'" method="post">';

              // Loop which will print form for each table to add rows
              for ($i=0; $i < count($column_array); $i++) {

                $placeholder_text = '';

                // Check if the column has either a "AUTO_INCREMENT" label..
                if ($i == 0) {
                  if ($this->checkColumn($table[0], $column_array[0], "extra='auto_increment'")) {
                    $placeholder_text = 'Auto';
                  }
                }
                // ..or a "NOT NULL" label
                else {
                  if ($this->checkColumn($table[0], $column_array[$i], "is_nullable='NO'")) {
                    $placeholder_text = "Not null";
                  }
                }

                echo '<td><input size="'.strlen($row_length_array[$i]).'" type="text"
                  name="'.$i.'_field_'.$table[0].'"placeholder="'.$placeholder_text.'"</td>';
              }
              echo '<td id="empty_table"><input class="blue" id="add_row_input" type="submit"
                name="add_row_'.$table[0].'" value="Add row"></td></form></tr>';


            // Echoes a form where you'll be able to remove columns
            echo '</table><div id="remove_column" class="below_form">
                  <h4>Remove column:</h4><form
                  action="'.$this->removeColumn($column_array[0], $table[0]).'" method="post">
                  <select name="'.$table[0].$table[0].'">';
                  foreach ($column_array as $column) {
                    echo '<option value='.$column.'>'.$column.'</option>';
                  }
            echo '</select><p><input class="red" type="submit"
                  name="'.$column_array[0].$table[0].'"value="Remove"></p></form></div>';


            // Echoes a form where you'll be able to add columns
            echo '<div id="add_column" class="below_form">
                  <h4>Add column:</h4><form
                  action="'.$this->addColumn($table[0]).'" method="post">
                  <input type="text" name="column_name" placeholder="Column\'s name">
                  <input id="dt_text" type="text" name="column_dt" placeholder="Datatype">
                  <input type="checkbox" name="not_null">Not null
                  <p><input class="blue" type="submit" name="add_column'.$table[0].'"
                  value="Add"></p></form></div>';

            // Echoes a form where you'll be able to update fields
            echo '<div id="update_field" class="below_form">
                  <h4>Update value:</h4><form
                  action="'.$this->updateTableField($table[0], $column_array[0]).'"
                  method="post"><select name="column'.$table[0].'">
                  <option value="" disabled selected>Column</option>';
                  foreach ($column_array as $column) {
                    echo '<option value='.$column.'>'.$column.'</option>';
                  }
            echo  '</select><select name="row_id_value">
                   <option value="" disabled selected>Row</option>';
                   foreach ($id_value_array as $value) {
                     echo '<option value='.$value.'>'.$value.'</option>';
                   }
            echo '</select>
                  <input type="text" name="field_value" placeholder="New value">
                  <p><input class="blue" type="submit" value="Update"
                  name="update_field'.$table[0].'"></p></div><hr>';
          }
        }
        else {
          echo '<p>Could not find any tables.</p>';
        }
      }


      /**
       * This function will check if a column has any specific labels.
       * When user/function wants to know whether a specific column has any
       * labels, it'll run through this function. To know which column to find
       * there are 2 parameters to do the job. In case there are rows to be found,
       * function will return true to it's caller.
       *
       * @var STRING $query - query to be executed to the MySQL
       *
       * @param  STRING $table - table's name
       * @param  STRING $column - column's name
       * @param  STRING $condition - condition included in MySQL query
       * @example "extra='auto_increment'"
       * @example "is_nullable='NO'"
       *
       * @return TRUE/FALSE - returns true/false to the called function
       *
       */
      function checkColumn ($table, $column, $condition) {

        $query = "SELECT column_name FROM INFORMATION_SCHEMA.COLUMNS
                  WHERE table_name='$table' AND column_name='$column'
                  AND $condition;";

        return (mysqli_num_rows($this->connection, $query) > 0 ? true : false);
        //
        // if (mysqli_num_rows(mysqli_query($this->connection, $query)) > 0) {
        //   return true;
        // }
        // else {
        //   return false;
        // }
      }


      /**
       * [updateTableValue description]
       *
       * @return [type] [description]
       *
       */
      function updateTableField ($table, $key_column) {

        if (isset($_POST['column'.$table], $_POST['row_id_value'],
          $_POST['update_field'.$table])) {

          $column = $_POST['column'.$table];
          $row_value = $_POST['row_id_value'];
          $value = $_POST['field_value'];

          $query = "UPDATE $table
                    SET $column='$value'
                    WHERE $key_column='$row_value';";

          if (!mysqli_query($this->connection, $query)) {
            $this->printError();
          }
          header('Location: '.$_SERVER['PHP_SELF']);
        }
      }



      /**
       * Function which prints total number of databases, tables, columns and rows.
       *
       * The main purpose of this function is to print the wanted number of specific
       * elements inside all of the databases. The main page already has a <table>
       * layout so this'll only add <td> - rows inside of it. All the statistics are
       * counted inside while - loops to get the accurate numbers.
       *
       * @var INT $db_number = the amount of databases
       * @var INT $table_number = the amount of tables
       * @var INT $column_number = the amount of columns
       * @var INT $row_number = the amount of rows
       *
       * @var MYSQL_QUERY $db_query - a query to fetch databases
       * @var MYSQL_QUERY $table_query - a query to fetch tables
       * @var MYSQL_QUERY $column_query - a query to fetch columns
       * @var MYSQL_QUERY $row_query - a query to fetch rows
       *
       * @var ARRAY $db - array which contains all of the databases
       * @var ARRAY $table - array which contains all of the tables
       * @var ARRAY $column - array which contains all of the columns
       *
       */
      function getInformation () {

        $table_number = 0;
        $column_number = 0;
        $row_number = 0;

        $db_query = "SHOW DATABASES WHERE `Database` NOT LIKE '%schema%' AND
                    `Database` NOT IN ('phpmyadmin', 'mysql', 'sys')";

        if ($_SESSION['safe_mode']) {
          $db_query .= " AND `Database` LIKE ('safe_mode_%')";
        }
        else {
          $db_query .= " AND `Database` NOT LIKE ('safe_mode_%')";
        }

        $db_query = mysqli_query($this->connection, $db_query);

        if (!$db_query) {
          $this->printError();
        }

        // The amount of databases
        $db_number = mysqli_num_rows($db_query);
        echo '<td>'.$db_number.'</td>';

        while ($db = mysqli_fetch_array($db_query))
        {
          // Count tables
          mysqli_select_db($this->connection, $db[0]);
          $table_query = mysqli_query($this->connection, "SHOW TABLES FROM `$db[0]`;");
          $table_number += mysqli_num_rows($table_query);

          // Count columns
          while ($table = mysqli_fetch_array($table_query))
          {
            $column_query = mysqli_query($this->connection, "SHOW COLUMNS FROM `$table[0]`;");
            $column_number += mysqli_num_rows($column_query);

            // Count rows
            $row_query = mysqli_query($this->connection, "SELECT * FROM `$table[0]`;");
            $row_number += mysqli_num_rows($row_query);
          }
        }
        // The amount of tables
        echo '<td>'.$table_number.'</td>';
        // The amount of columns
        echo '<td>'.$column_number.'</td>';
        // The amount of rows
        echo '<td>'.$row_number.'</td>';
      }



      /**
       * Function which will delete all of the databases.
       *
       * This function will go over all of the databases (excluding "information
       * databases") and delete them one by one. It'll print an error message
       * in case the deletion of the database is unsuccessful. Finally the
       * function will remove all session variables and refreshes the page.
       *
       * @var MYSQLI_QUERY $db_query - query to find all the databases
       *
       * @var ARRAY $db - array which includes all the databases
       * @var STRING $query - deletion-query for one database at a time
       *
       */
      function deleteAllDatabases () {

        if (isset($_POST['delete_all'])) {

          $db_query = mysqli_query($this->connection,
            // exclude information databases
            "SHOW DATABASES WHERE `Database` NOT LIKE '%schema%' AND
            `Database` NOT IN ('phpmyadmin', 'mysql', 'sys');");

          // Atleast one database has to be found
          if (mysqli_num_rows($db_query) > 0) {
            while ($db = mysqli_fetch_array($db_query)) {
              $query = "DROP DATABASE IF EXISTS `$db[0]`";
              if (!mysqli_query($this->connection, $query)) {
                $this->printError();
              }
            }

            unset($_SESSION['search']);
            unset($_SESSION['database']);
            header('Location: '.$_SERVER['PHP_SELF']);
          }
        }
      }



      /**
       * Function which creates multiple databases at once with content.
       *
       * This function will use four ready-made templates for database and it's
       * content. Five databases will be done at user's command. In every loop
       * the database will receive a random number to it's name so they can be
       * made endessly. After every multi_query the memory will be freed from the
       * result so server's memory won't ramp up. Finally the page will be reloaded.
       *
       * @var ARRAY $db_names - an array which contains 5 names for databases
       * @var ARRAY $db_content - four different contains for databases
       *
       * @var INT $random_nmb - contains random number which will unite with the
       *  name of the database. With this you can endessly create new databases
       * @var STRING $result - result of the query inside variable so it can be
       *  freed from the server
       *
       */
      function massCreateDatabases () {

        if (isset($_POST['create_db_many'])) {

          $db_names = array('Basketball_db', 'Chainstore_db', 'Steam_db', 'Library_db', 'Empty_db');
          $db_content = array($this->basketball, $this->chainstore, $this->steam, $this->library);

          for ($i=0; $i < 4; $i++) {

            $random_nmb = mt_rand(10, 100);
            // Give an unique name to database
            $db_names[$i] .= '_'.$random_nmb;

            if ($_SESSION['safe_mode']) {
              $db_names[$i] = 'safe_mode_'.$db_names[$i];
            }

            if (mysqli_query($this->connection, "CREATE DATABASE IF NOT EXISTS `$db_names[$i]`;")) {
              mysqli_select_db($this->connection, $db_names[$i]);
              if ($result = mysqli_multi_query($this->connection, $db_content[$i])) {
                do {
                  mysqli_free_result($result);
                } while (mysqli_next_result($this->connection));
              }
            }
          }
          header('Location: '.$_SERVER['PHP_SELF']);
        }
      }



      /**
       * Function which truncates all tables in one database.
       *
       * This function will truncate all databases from specific database. It'll
       * firstly check that database has been checked. After that it stores all the
       * tables from that database into an array which will then be looped through
       * a while-statement. Foreign key -checks will be disabled for that remaining time
       * which will disable all the key constraint errors. This will make truncating
       * flawless. Finally the page will be refreshed.
       *
       * @var MYSQLI_QUERY $table_query - query to MySQL which will fetch all the tables
       *  in a specific database
       * @var ARRAY $table - contains all the names of the tables in an array
       * @var MYSQLI_QUERY $query - query to MySQL which will try to truncate that database
       *
       */
      function truncateAllTables () {

        if (isset($_SESSION['database']) && isset($_POST['truncate_tbl'])) {

          mysqli_select_db($this->connection, $_SESSION['database']);

          $table_query = mysqli_query($this->connection, "SHOW TABLES;");

          # Remove foreign checks = no errors on foreign keys */
          mysqli_query($this->connection, "SET FOREIGN_KEY_CHECKS = 0;");
          while ($table = mysqli_fetch_array($table_query)) {
            $query = mysqli_query($this->connection, "TRUNCATE TABLE `$table[0]`;");
          }
          mysqli_query($this->connection, "SET FOREIGN_KEY_CHECKS = 1;");
          header('Location: '.$_SERVER['PHP_SELF']);
        }
      }



      /**
       * Function which truncates the whole database.
       *
       * This function first takes the current database to the variable. It'll
       * then delete that specific database. Afterwards it'll be instantly created
       * again making the "illusion" of truncation. The page will be refreshed in
       * the end.
       *
       * @var STRING $db - a string which contains the name of current database
       * @var MYSQL_QUERY $query_delete - a query which deletes selected database
       * @var MYSQL_QUERY $query_delete - a query which creates just deleted database
       *
       */
      function truncateDatabase () {

        if (isset($_SESSION['database']) && isset($_POST['truncate_db'])) {

          $db = $_SESSION['database'];
          $query_delete = mysqli_query($this->connection, "DROP DATABASE IF EXISTS `$db`;");
          $query_create = mysqli_query($this->connection, "CREATE DATABASE `$db`;");

          header('Location: '.$_SERVER['PHP_SELF']);
        }
      }



      /**
       * Function which confirms the search.
       *
       * If search-button has been pressed and a search word has been given, this
       * function will save that searchword inside the session variable. This will
       * automatically start the search functions in the main page. Page is refreshed
       * in the end.
       *
       */
      function confirmSearch () {

        if (isset($_POST['search']) && !empty($_POST['searchword']))
        {
          $_SESSION['search'] = mysqli_real_escape_string($this->connection, $_POST['searchword']);
          header('Location: '.$_SERVER['PHP_SELF']);
        }
      }



      /**
       * Function which dismisses the search.
       *
       * After dismissing the search, this function will unset the search-session
       * variable from session. Finally the page will be refreshed.
       *
       */
      function removeSearch () {

        if (isset($_POST['hide_search']))
        {
          unset($_SESSION['search']);
          header('Location: '.$_SERVER['PHP_SELF']);
        }
      }



      /**
       * Search words from database - function.
       *
       * After the user has given a search word (which will go through the
       * confirmSearch(); function first) this function will be executed automatically.
       * It'll print the whole database like normally but will instead insert a keyword
       * to the middle of the SQL-query. If the given word is anywhere within one row,
       * the whole row will be printed. Like normally, the database will printed inside
       * of a table layout.
       *
       * @var STRING $db - name of the current database
       * @var STRING $searchword - a piece of string being searched through the database
       * @var STRING $query - working SQL-query which is being built by the search word
       *  included by the user. Every column's row goes through "LIKE %KEYWORD%" treatment
       *  where function knows if the row is being included in the printing session. To
       *  avoid errors last three characters are being deleted in the end of loop ( OR).
       *
       * @example SELECT * FROM `Lainaus` WHERE `lainaus` LIKE '%123%' OR `lainauspaiva`
       *          LIKE '%123%' OR `erapaiva` LIKE '%123%' OR `asiakas` LIKE '%123%'
       *
       * @var MYSQL_QUERY $table_query - SQL-query which returns all tables
       * @var MYSQL_QUERY $column_query - SQL-query which returns all columns
       *
       * @var ARRAY $table - contains all the tables inside database
       * @var ARRAY $column - contains all columns inside table
       * @var ARRAY $row - those rows which has the keyword will be included
       * @var STRING $value - row's values being printed out in the end
       *
       */
      function search () {

        if (isset($_SESSION['search'])) {

          $db = $_SESSION['database'];
          $searchword = $_SESSION['search'];

          echo '<h2>'.$db.'</h2><hr>';
          echo '<h3>Result(s) for word: </h3><p><i>'.$searchword.'</i></p><hr>';
          mysqli_select_db($this->connection, $db);

          $table_query = mysqli_query($this->connection, "SHOW TABLES;");
          while ($table = mysqli_fetch_array($table_query))
          {
            echo '<h3>'.$table[0].'</h3>';
            $column_query = mysqli_query($this->connection, "SHOW COLUMNS FROM $table[0];");
            echo '<table class="db_table"><tr>';
            $query = "SELECT * FROM `$table[0]` WHERE ";

            while ($column = mysqli_fetch_array($column_query))
            {
              echo '<th>'.$column[0].'</th>';
              $query .= "`$column[0]` LIKE '%$searchword%' OR ";
            }
            echo '</tr><tr>';

            // Remove last three characters from the query
            $query = substr($query, 0, -3);

            $query = mysqli_query($this->connection, $query);
            if (mysqli_num_rows($query) > 0) {
              while ($row = mysqli_fetch_assoc($query)) {
                foreach ($row as $value) {
                  echo '<td>'.$value.'</td>';
                }
                echo '</tr>';
              }
            }
            echo '</table>';
          }
        }
      }


}

?>
