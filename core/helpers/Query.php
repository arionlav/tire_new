<?php
namespace core\helpers;

use config\App;

/**
 * Class Query provide logic for more easiest work with MySQL databases
 *
 * @package core\helpers
 */
class Query
{
    /**
     * @var \PDO
     */
    protected $db;

    /**
     * @var string SQL query
     */
    protected $sql;

    /**
     * Create connection to database and set new \PDO object into $this->db
     *
     * @param string $dbName Database name
     */
    function __construct($dbName = 'tire_new')
    {
        App::$app->dbConnect($dbName);
        $this->db = App::$app->_db;
    }

    /**
     * Start write SQL query string: SELECT 'name column 1', 'name column 2'...
     *
     * @param array $params   Columns name
     * @param bool  $distinct True, if you need sql query SELECT DISTINCT
     * @return Query $this
     * @throws GenerateException
     */
    public function select(array $params, $distinct = false)
    {
        $this->sql = '';

        ($distinct === true)
            ? $select = "SELECT DISTINCT"
            : $select = "SELECT";

        $i     = 1;
        $count = count($params);

        if (! empty($params)) {
            foreach ($params as $p) {
                $select .= ' ' . $p;
                if ($i != $count) {
                    $select .= ',';
                }
                $i++;
            }
        } else {
            GenerateException::getException('Select any column', __CLASS__, __LINE__);
        }

        $this->sql = $select;

        return $this;
    }

    /**
     * Start write SQL query string: INSERT INTO '$table' VALUES (column1 = ':column1', ...)
     * Next call must be method $this->prepareBindStatement() and execute(['column1' => $variable])
     * Before them, we can call support methods as $this->whereBindStmt() etc.
     *
     * @param string $table  Table name
     * @param array  $params 'column name' => 'bind variable' pairs. Example: ['column1' => ':column1', ...]
     * @return Query $this
     */
    public function insertInto($table, array $params)
    {
        $this->sql = '';
        $insertStr = 'INSERT INTO ' . $table . ' (';
        $valuesStr = ' VALUES (';
        $i         = 1;
        $count     = count($params);

        foreach ($params as $pKey => $pVal) {
            if ($i == 1) {
                $insertStr .= $pKey;
                $valuesStr .= $pVal;
            } else {
                $insertStr .= ' ' . $pKey;
                $valuesStr .= $pVal;
            }

            if ($i != $count) {
                $insertStr .= ',';
                $valuesStr .= ',';
            } else {
                $insertStr .= ')';
                $valuesStr .= ')';
            }
            $i++;
        }

        $requersStr = $insertStr . $valuesStr;
        $this->sql  = $requersStr;

        return $this;
    }

    /**
     * Start write SQL query string: DELETE FROM '$table'
     *
     * @param string $table Table name
     * @return Query $this
     * @throws GenerateException
     */
    public function deleteFrom($table)
    {
        $this->sql = '';
        if (is_string($table) and $table != '') {
            $this->sql = 'DELETE FROM ' . $table;
        } else {
            GenerateException::getException('Name table for deleting is wrong');
        }

        return $this;
    }

    /**
     * Start write SQL query string: UPDATE '$table' SET 'column1' = ':column1', ...
     *
     * @param string $table  Table name
     * @param array  $params 'column name' => 'bind variable' pairs. Example: ['column1' => ':column1', ...]
     * @param bool   $custom Set true, if value need to be quoted
     * @return Query $this
     */
    public function updateBindParam($table, array $params, $custom = false)
    {
        $this->sql = '';
        $insertStr = 'UPDATE ' . $table . ' SET ';

        $i = 1;
        if (! empty($params)) {
            foreach ($params as $pKey => $pVal) {
                if ($custom === false) {
                    if (is_string($pVal)) {
                        $pVal = "'" . $pVal . "'";
                    }
                }
                ($i == 1)
                    ? $insertStr .= $pKey . '=' . $pVal
                    : $insertStr .= ', ' . $pKey . '=' . $pVal;

                $i++;
            }
        }

        $this->sql = $insertStr;

        return $this;
    }

    /**
     * Add to $this->sql query string the part: FROM 'table name'
     *
     * @param string $from Table name
     * @return Query $this
     * @throws GenerateException
     */
    public function from($from)
    {
        $this->checkThisSql();

        if (isset($from) && is_string($from)) {
            $this->sql .= ' FROM ' . $from;
        } else {
            GenerateException::getException('Wrong param for FROM', __CLASS__, __LINE__);
        }

        return $this;
    }

    /**
     * Add to $this->sql query string the part: WHERE 'column1'=$variable1 AND ...
     *
     * @param array $params 'column1' => $variable1
     * @return Query $this
     */
    public function where(array $params)
    {
        $this->checkThisSql();

        $i = 1;
        foreach ($params as $pKey => $pVal) {
            ($i == 1)
                ? $this->sql .= ' WHERE ' . $pKey . '=' . '\'' . $pVal . '\''
                : $this->sql .= ' AND ' . $pKey . '=' . '\'' . $pVal . '\'';
            $i++;
        }

        return $this;
    }

    /**
     * Add to $this->sql query string the part: WHERE '$queryString' ...
     *
     * @param string $queryString SQL query string after 'WHERE'
     * @return Query $this
     */
    public function whereCustom($queryString)
    {
        if (is_string($queryString) and $queryString != '') {
            $this->sql .= ' WHERE ' . $queryString;
        }

        return $this;
    }

    /**
     * Create PDO statement with bind params
     * Example ...WHERE brand = :brand...
     *
     * @param array $params 'brand' => ':brand'
     * @return Query $this
     */
    public function whereBindStmt(array $params)
    {
        $this->checkThisSql();

        $i = 1;
        if (! empty($params)) {
            foreach ($params as $pKey => $pVal) {
                ($i == 1)
                    ? $this->sql .= ' WHERE ' . $pKey . ' = ' . $pVal
                    : $this->sql .= ' AND ' . $pKey . ' = ' . $pVal;
                $i++;
            }
        }

        return $this;
    }

    /**
     * Prepare PDO statement before method bindParam and executed
     *
     * @return \PDOStatement
     */
    public function prepareBindStatement()
    {
        return $this->db->prepare($this->sql);
    }

    /**
     * Execute PDO statement and run fetchAll method
     *
     * @param \PDOStatement $sth
     * @param null|array    $bindParams Example: $bindParams[':model' => $values]
     * @return array
     */
    public function executeBindStmt(\PDOStatement $sth, $bindParams = null)
    {
        $sth->execute($bindParams);

        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }


    /**
     * Execute PDO statement and run fetch method. Use where result is a single row
     *
     * @param \PDOStatement $sth
     * @return array
     */
    public function executeBindStmtOne(\PDOStatement $sth)
    {
        $sth->execute();

        $result = $sth->fetch(\PDO::FETCH_ASSOC);

        return $result;
    }


    /**
     * Add to $this->sql query string the part: ORDER BY '$orderBy' ...
     *
     * @param string $orderBy Column name
     * @return Query $this
     */
    public function orderBy($orderBy)
    {
        $this->checkThisSql();

        if (is_string($orderBy) and $orderBy != '') {
            $this->sql .= ' ORDER BY ' . $orderBy;
        }

        return $this;
    }


    /**
     * Add to $this->sql query string the part: INNER JOIN '$table ON '$expression'
     *
     * @param string $table      Table name
     * @param string $expression For example: 'tires.list = list.idList'
     * @return Query $this
     */
    public function innerJoin($table, $expression)
    {
        $this->checkThisSql();

        if (
            is_string($table) and
            is_string($expression) and
            $table != '' and
            $expression != ''
        ) {
            $this->sql .= ' INNER JOIN ' . $table . ' ON ' . $expression;
        }

        return $this;
    }


    /**
     * Check for empty query string $this->sql
     *
     * @return bool
     * @throws GenerateException
     */
    private function checkThisSql()
    {
        if ($this->sql == '') {
            GenerateException::getException('SQL query string is empty.
                At first, use method select, delete, etc.', __CLASS__, __LINE__);
        }

        return true;
    }


    /**
     * Truncate table $table
     *
     * @param string $table Table name
     * @return bool
     * @throws \PDOException
     */
    public function truncate($table)
    {
        try {
            $sql = "TRUNCATE TABLE $table";
            if (! $this->db->query($sql)) {
                throw new \PDOException('Table "' . $table . '" not found in DB');
            }
        } catch (\PDOException $e) {
            return false;
        }

        return true;
    }


    /**
     * Run \PDO method query() and fetch result.
     * Use, where result must be a single row
     *
     * @return bool|array
     * @throws \PDOException
     */
    public function one()
    {
        $this->checkThisSql();

        try {
            $stmt = $this->db->query($this->sql);
            if (is_object($stmt)) {
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            } else {
                throw new \PDOException('SQL was wrong, needs object');
            }
        } catch (\PDOException $e) {
            return false;
        };

        return $result;
    }


    /**
     * Run and fetchAll sql query
     *
     * @return array SQL query result
     * @throws \PDOException
     */
    public function all()
    {
        $this->checkThisSql();

        try {
            $stmt = $this->db->query($this->sql);
            if (is_object($stmt)) {
                $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } else {
                throw new \PDOException('SQL was wrong, needs object for fetch, Check your SQL syntax.');
            }
        } catch (\PDOException $e) {
            return false;
        }

        return $result;
    }


    /**
     * Run SQL query with \PDO method exec()
     *
     * @return false|int
     * @throw \PDOException
     */
    public function executeQuery()
    {
        $this->checkThisSql();

        try {
            $result = $this->db->exec($this->sql);
        } catch (\PDOException $e) {
            echo $e->getMessage();

            return false;
        }

        return $result;
    }


    /**
     * Run SQL query with \PDO method query()
     *
     * @return false|\PDOStatement
     */
    public function queryRequest()
    {
        try {
            $result = $this->db->query($this->sql);
        } catch (\PDOException $e) {
            echo $e->getMessage();

            return false;
        }

        return $result;
    }

    /**
     * @return \PDO
     */
    public function getDb()
    {
        return $this->db;
    }
}
