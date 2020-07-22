<?php


class CoreData{

    protected $db_table = '';
    protected $table_id = 'id';

    function  __construct($id=NULL,$caching=FALSE) {
        $wherearray = array();
        if(!is_null($id)){
            $wherearray[$this->table_id] = $id;
        }
        if($this->db_table == 'd_fias_addrobj')
            $wherearray['actstatus']=1;
        if(count($wherearray))
            $this->initItem($wherearray,$caching);

    }

    /**
     *  Filling Items parametres from `db_table`
     *
     * @param array $aWhere filter for fetching Item
     * @param bool $caching
     */
    private function initItem($aWhere,$caching=FALSE)
    {

        $ignore = array();
        $dba = DB::getAdaptor();
        $sql = $dba->createSafeSELECT('*', $this->db_table, $aWhere,  $this->table_id,FALSE,1);
//        echo $sql;
//        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
//        exit;
        $from = $dba->getObject($sql,$caching);
        $reflect = new ReflectionObject($this);

        foreach ($reflect->getProperties() as $prop){
            $propname = $prop->getName();
            if (!in_array( $propname, $ignore ) && isset( $from->$propname )){
                $this->$propname = $from->$propname;
            }
        }
    }

    /**
     * Update row in DB with class properties values
     *
     * @param array $aEcxeptFields
     */
    protected function updateItem($aEcxeptFields = array())
    {
        $aUPDStr = array();
        $dba = DB::getAdaptor();
        $reflect = new ReflectionObject($this);
        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $propname = $prop->getName();
            if($propname==$this->table_id)continue;
            if (in_array($propname,$aEcxeptFields)) {
                continue;
            }

            $aUPDStr[$propname] = $this->$propname;
        }

        $dba->update($this->db_table, $aUPDStr, array("{$this->table_id}"=>$this->{$this->table_id}));

    }

    /**
     * Insert new row in DB and fill it by values
     * @param $db_table
     * @param array $aColumnsValues values for new row
     * @return bool|CoreData
     */
    protected static function newItem($db_table,$aColumnsValues){
        $dba = DB::getAdaptor();
        $id = $dba->insert($db_table, $aColumnsValues);
        if($id){
            return new static($id);
        }else
            return FALSE;
    }


    /**
     * Delete current row from table
     */
    protected  function deleteItem()
    {
        $dba = DB::getAdaptor();
        $dba->delete($this->db_table, array("{$this->table_id}"=>$this->{$this->table_id}));

    }

    /**
     * Create array of objects by there's IDs
     *
     * @param array $aIDs
     * @param bool $caching
     * @return array
     */
    public static function composeObjectList($aIDs = array(),$caching=FALSE)
    {
        $aRetVal = array();
        foreach ($aIDs as $id) {
            $aRetVal[] = new static($id,$caching);
        }
        return $aRetVal;
    }

}
