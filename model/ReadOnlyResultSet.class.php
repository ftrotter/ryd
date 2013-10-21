<?php 
/**
 * contains the recording model class.
 * @author The guy who wrote the article
 * @package YDA
 * @todo remove me?
 */
class ReadOnlyResultSet {
    private $rs;
    
    function __construct($rs)
    {
        $this->rs = $rs;
    }
    
    function getNext($dataobject)
    {
        $row = mysql_fetch_array($this->rs);

        $class = new ReflectionObject($dataobject);
        $properties = $class->getProperties();

        for ($i = 0; $i < count($properties); $i++){
            $prop_name = $properties[$i]->getName();
            $dataobject->$prop_name = $row[$prop_name];
        }
        
        return $dataobject;
    }
    
    function reset()
    {
        mysql_data_seek($this->rs, 0);
    }
    
    function rowCount()
    {
        return mysql_num_rows($this->rs);
    }
}
?>
