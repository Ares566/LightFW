<?php

/**
 * Class DB
 * Easy working with PDO
 *
 * User: Renat Abaidulin
 * Date: 09.08.2019
 */
class DB
{

    private $dbh;

    /**
     * DB constructor.
     * @param string $dbname
     * @param string $dbuser
     * @param string $dbpassword
     */
    function __construct($dbname = '', $dbuser = '', $dbpassword = '')
    {
        $_dbname = $dbname ? $dbname : Settings::$dbname;
        $_dbuser = $dbuser ? $dbuser : Settings::$dbuser;
        $_dbpassword = $dbpassword ? $dbpassword : Settings::$dbpassword;

        $this->dbh  = new PDO('pgsql:host=localhost;dbname=' . $_dbname . ';charset=utf8', $_dbuser, $_dbpassword);
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    }


    /**
     * Returns array of objects accroding $sql
     * @param $sql
     * @param bool $caching
     * @return array|mixed
     */
    public function getObjectList($sql, $caching = FALSE)
    {
        $id_key = crc32($sql . '_getObjectList');
        if ($caching) {
            $mcdata = Utility::getMCData($id_key);
            if ($mcdata) {
                $unMCData = unserialize($mcdata);
                if (count($unMCData))
                    return $unMCData;
            }
        }

        $query = $this->execute($sql);
        $retdata = $query->fetchAll(PDO::FETCH_CLASS);

        if (count($retdata) && $caching) {
            Utility::setMCData($id_key, serialize($retdata), $caching);
        }

        return $retdata;
    }

    /**
     * Return one Object
     * @param $sql
     * @param bool $caching
     * @return mixed
     */
    public function getObject($sql, $caching = FALSE)
    {
        $id_key = crc32($sql . '_getObject');
        if ($caching) {
            $mcdata = Utility::getMCData($id_key);
            if ($mcdata) {
                $unMCData = unserialize($mcdata);
                if (count($unMCData))
                    return $unMCData;
            }
        }

        $query = $this->execute($sql);
        $retdata = $query->fetchObject();

        if ($retdata !== FALSE && $caching) {
            Utility::setMCData($id_key, serialize($retdata), $caching);
        }

        return $retdata;
    }

    /**
     * Return only one value.
     * First column of first row
     *
     * @param $sql
     * @param bool $caching
     * @return int|mixed
     */
    public function getOneValue($sql, $caching = FALSE)
    {
        $id_key = crc32($sql . '_getOneValue');
        if ($caching) {
            $mcdata = Utility::getMCData($id_key);
            if ($mcdata)
                return $mcdata;
        }

        $query = $this->execute($sql);
        $retdata = $query->fetchColumn();
        if ($retdata !== FALSE && $caching) {
            Utility::setMCData($id_key, $retdata, $caching);
        }

        return $retdata;
    }

    /**
     * Return array of first column
     * @param $sql
     * @param bool $caching
     * @return array|mixed
     */
    public function getColumnArray($sql, $caching = FALSE)
    {
        $id_key = crc32($sql . '_getColumnArray');
        if ($caching) {
            $mcdata = Utility::getMCData($id_key);
            if ($mcdata) {
                $unMCData = unserialize($mcdata);
                if (count($unMCData))
                    return $unMCData;
            }
        }

        $query = $this->execute($sql);
        $retdata = $query->fetchAll(PDO::FETCH_COLUMN, 0);
        if (count($retdata) && $caching) {
            Utility::setMCData($id_key, serialize($retdata), $caching);
        }

        return $retdata;
    }

    /**
     * Insert values into table
     *
     * @param $table
     * @param array $aColumnsValues column=>value
     * @param string $onDuplicateKey
     * @return mixed last insert id or false
     */
    public function insert($table, $aColumnsValues = array(), $onDuplicateKey = '')
    {
        if (!count($aColumnsValues) || !$table)
            return FALSE;

        $sql = 'INSERT INTO `' . $table . '`';
        $aTKeys = array();
        $aTVals = array();
        foreach ($aColumnsValues as $key => $value) {
            $aTKeys[] = "`$key`";
            $aTVals[] = $this->dbh->quote($value);
        }
        $sql .= '(' . implode(',', $aTKeys) . ') VALUES (' . implode(',', $aTVals) . ')';
        if ($onDuplicateKey != '') {
            $sql .= ' ON CONFLICT DO UPDATE SET ' . $onDuplicateKey . ' ';
        }
        $sql .= ';';

        $query = $this->execute($sql);

        return $query ? $this->dbh->lastInsertId() : FALSE;
    }

    /**
     * Update values in table
     *
     * @param $table
     * @param array $aColumnsValues column=>new_value
     * @param array $aWhere filter for where column=>value
     * @return bool
     */
    public function update($table, $aColumnsValues = array(), $aWhere = array())
    {
        if (!count($aColumnsValues) || !count($aWhere) || !$table)
            return FALSE;

        $sql = 'UPDATE `' . $table . '` SET ';

        $aTVals = array();
        foreach ($aColumnsValues as $key => $value) {
            $aTVals[] = "`$key`=" . $this->dbh->quote($value);
        }
        $sql .= implode(',', $aTVals);

        $aTVals = array();
        foreach ($aWhere as $key => $value) {
            $aTVals[] = "`$key`=" . $this->dbh->quote($value);
        }
        $sql .= ' WHERE ' . implode(' AND ', $aTVals);

        return $this->execute($sql);

    }

    /**
     * Delete row from table according filter
     * @param $table
     * @param array $aWhere filter
     * @return bool
     */
    public function delete($table, $aWhere = array())
    {
        if (!count($aWhere) || !$table)
            return FALSE;

        $sql = 'DELETE FROM `' . $table . '` WHERE ';

        $aTVals = array();
        foreach ($aWhere as $key => $value) {
            $aTVals[] = "`$key`=" . $this->dbh->quote($value);
        }

        $sql .= implode(' AND ', $aTVals);
        return $this->execute($sql);

    }


    /**
     * @param $sql
     * @return mixed
     */
    public function execute($sql)
    {
        //echo $sql."\n";
        $query = NULL;
        $query = $this->dbh->prepare($sql);
        if($query->execute())
            return $query;
        else
            return false;

    }

    public function quote($value)
    {
        return $this->dbh->quote($value);
    }

    /**
     * Return escaped string
     * @param $text
     * @param bool $extra
     * @return mixed|string
     */
    public function getEscaped($text, $extra = false)
    {
        $result = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $text);
        if ($extra) {
            $result = addcslashes($result, '%_');
        }
        return $result;
    }

    /**
     * Singletone get new DB()
     * @return DB|null
     */
    static public function getAdaptor()
    {
        static $dbconnector = null;
        if (!isset($dbconnector)) {
            $dbconnector = new DB();
        }
        return $dbconnector;
    }

    public function createSafeSELECT($column = '*', $table, $aWhere = array(), $orderby = '', $asc = TRUE, $limit = 0, $offset = 0)
    {

        if (!$table)
            return FALSE;

        $sql = 'SELECT ';
        if ($column == '*' || !is_array($column) || !count($column))
            $sql .= $column;
        else {
            $sql .= implode(',', $column);
        }
        $sql .= ' FROM `' . $table . '` ';

        if (count($aWhere)) {
            $aTVals = array();
            foreach ($aWhere as $key => $value) {
                $aTVals[] = "`$key`=" . $this->dbh->quote($value);
            }
            $sql .= ' WHERE ' . implode(' AND ', $aTVals);
        }
        if ($orderby)
            $sql .= ' ORDER BY `' . $orderby . '` ' . ($asc ? 'ASC' : 'DESC');

        if ($limit)
            $sql .= ' LIMIT ' . intval($limit);
        if ($offset)
            $sql .= ' OFFSET ' . intval($offset);

        return $sql;
    }

}
