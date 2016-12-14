<?php
/**
 * Oci wrap class
 *
 * PHP Version 5
 *
 * @category Driver
 * @package  Opensymap
 * @author   Pietro Celeste <p.celeste@opensymap.org>
 * @license  GPL http://www.gnu.org/licenses/gpl-3.0.en.html
 * @link     http://docs.opensymap.org/ref/Osy
 */
namespace Osynapsy\Core\Driver;

class DbOci
{
    private $__par = array();
    private $__cur = null;
    public  $backticks = '"';
    public  $cn = null;
    private $__transaction = false;
    //private $rs;

    public function __construct($str)
    {
        $par = explode(':',$str);
        $this->__par['typ'] = trim($par[0]);
        $this->__par['hst'] = trim($par[1]);
        $this->__par['db']  = trim($par[2]);
        $this->__par['usr'] = trim($par[3]);
        $this->__par['pwd'] = trim($par[4]);
        $this->__par['query-parameter-dummy'] = 'pos';
    }
    
    public function begin()
    {
        $this->beginTransaction();
    }
    
    public function beginTransaction()
    {
        $this->__transaction = true;
    }

    public function columnCount()
    {
       return $this->__cur->columnCount();
    }

    public function commit()
    {
        oci_commit($this->cn );
    }

    public function rollback()
    {
        oci_rollback($this->cn );
    }

    public function connect()
    {
        $this->cn = oci_connect(
            $this->__par['usr'],
            $this->__par['pwd'],
            "{$this->__par['hst']}/{$this->__par['db']}",
            'AL32UTF8'
        );
        if (!$this->cn) {
            $e = oci_error();
            trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
        } else {
            $this->execCommand("ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD'");
        }
    }

    function getType()
    {
       return 'oracle';
    }

    //Metodo che setta il parametri della connessione
    function setParam($p,$v)
    {
      $this->__par[$p] = $v;
    }

    //Prendo l'ultimo valore di un campo autoincrement dopo l'inserimento
    public function lastInsertId($arg)
    {
        foreach ($arg as $k => $v) {
            if (strpos('KEY_',$k) !== false) {
                return $v;
            }
        }
    }

    public function execMulti($cmd, $par)
    {
        $this->beginTransaction();
        $s = $this->prepare($cmd);
        foreach ($par as $rec) {
            try {
                $s->execute($rec);
            } catch (Exception $e){
                echo $e;
                var_dump($rec);
                return;
            }
        }
        $this->commit();
    }

    public function execCommand($cmd, $par = null, $rs_return = true)
    {
        $rs = oci_parse($this->cn, $cmd);
        if (!$rs) {
            $e = oci_error($this->cn);  // For oci_parse errors pass the connection handle
            throw new \Exception($e['message']);
            return;
        }
        if (!empty($par) && is_array($par)) {
            foreach ($par as $k => $v) {
                $$k = $v;
                // oci_bind_by_name($rs, $k, $v) does not work
                // because it binds each placeholder to the same location: $v
                // instead use the actual location of the data: $$k
                $l = strlen($v) > 255 ? strlen($v) : 255;
                oci_bind_by_name($rs, ':'.$k, $$k, $l);
            }
        }
        
        $ok = $this->__transaction ? oci_execute($rs, OCI_NO_AUTO_COMMIT) : oci_execute($rs);
        
        if (!$ok) {
            $e = oci_error($rs);  // For oci_parse errors pass the connection handle
            throw new \Exception($e['message'].PHP_EOL.$e['sqltext'].PHP_EOL.print_r($par,true));
            return;
        } 
        
        if ($rs_return) {
            return $rs;
        } 
        
        foreach ($par as $k=>$v) {
            $par[$k] = $$k;
        }
        oci_free_statement($rs);
        return $par;
    }

    public function execQuery($sql, $par = null, $fetchMethod = null)
    {
        $this->__cur = $this->execCommand($sql, $par);
        switch ($fetchMethod) {
            case 'BOTH':
                $fetchMethod = OCI_BOTH;
                break;
            case 'NUM':
                $fetchMethod = OCI_NUM;
                break;
            default:
                $fetchMethod = OCI_ASSOC;
                break;
        }
        oci_fetch_all($this->__cur, $result, null, null, OCI_FETCHSTATEMENT_BY_ROW|OCI_RETURN_NULLS|OCI_RETURN_LOBS|$fetchMethod);
        //oci_free_statement($cur);
        return $result;
    }

    public function query($sql)
    {
        return $this->execCommand($sql);
    }

    public function fetchAll2($rs)
    {
        oci_fetch_all($rs, $res, null, null, OCI_FETCHSTATEMENT_BY_ROW|OCI_ASSOC|OCI_RETURN_NULLS|OCI_RETURN_LOBS);
        return $res;
    }

    public function fetchAll($rs)
    {
        $result = array();
        while ($record = oci_fetch_array($rs, OCI_ASSOC|OCI_RETURN_NULLS)) {
            $result[] = $record;
        }
        return $result;
    }

    public function execUnique($sql, $par = null, $mth = 'NUM')
    {
       $res = $this->execQuery($sql, $par, $mth);
       if (empty($res)) return null;
       return (count($res[0])==1) ? $res[0][0] : $res[0];
    }

    public function getColumns($stmt = null)
    {
        $stmt = is_null($stmt) ? $this->__cur : $stmt;
        $cols = array();
        $ncol = oci_num_fields($stmt);
        for ($i = 1; $i <= $ncol; $i++) {
            $cols[] = array(
                'native_type' => oci_field_type($stmt,$i),
                'flags' => array(),
                'name' => oci_field_name($stmt,$i),
                'len' => oci_field_size($stmt,$i),
                'pdo_type' => oci_field_type_raw($stmt,$i)
            );
        }
        return $cols;
    }

    public function insert($table, $values, $keys = array())
    {
        $command  = 'INSERT INTO '.$table;
        $command .= '('.implode(',', array_keys($values)).')';
        $command .= ' VALUES ';
        $command .= '(:'.implode(',:',array_keys($values)).')';
        if (is_array($keys) && !empty($keys)) {
            $command .= ' RETURNING ';
            $command .= implode(',',array_keys($keys));
            $command .= ' INTO ';
            $command .= ':KEY_'.implode(',:KEY_',array_keys($keys));
            foreach ($keys as $k => $v) {
                $values['KEY_'.$k] = null;
            }
        }
        $values = $this->execCommand($command, $values, false);
        $res = array();
        foreach ($values as $k => $v) {
            if (strpos($k,'KEY_') !== false) {
                $res[str_replace('KEY_','',$k)] = $v;
            }
        }
        return $res;
    }

    public function update($table, array $values, array $condition)
    {
        $fields = array();
        $where = array();
        foreach ($values as $field => $value) {
            $fields[] = "{$field} = :{$field}";
        }
        foreach ($condition as $field => $value) {
            if (is_null($value)) {
                $where[] = "$field is null";
                continue;
            }
            $where[] = "$field = :WHERE_{$field}";
            $values['WHERE_'.$field] = $value;
        }
        $cmd .= 'UPDATE '.$table.' SET ';
        $cmd .= implode(', ',$fields);
        $cmd .= ' WHERE ';
        $cmd .= implode(' AND ',$where);
        return $this->execCommand($cmd, $values);
    }

    public function delete($table, $keys)
    {
        $where = array();
        if (!is_array($keys)){ 
            $keys = array('id'=>$cnd);
        }
        foreach($keys as $k=>$v){
            $where[] = "{$k} = :{$k}";
        }
        $cmd  = 'DELETE FROM '.$table;
        $cmd .= ' WHERE '.implode(' AND ',$where);
        $this->execCommand($cmd, $keys);
    }

    public function par($p)
    {
        return array_key_exists($p,$this->__par) ? $this->__par[$p] : null;
    }

    public function cast($field,$type)
    {
        $cast = $field;
        switch ($this->get_type()) {
            case 'pgsql':
                         $cast .= '::'.$type;
                         break;
        }
        return $cast;
    }

    public function freeRs($rs)
    {
        oci_free_statement($rs);
    }

    public function close()
    {
        oci_close($this->cn);
    }
    
    public function dateToSql($date)
    {
        $app = explode('/',$date);
        if (count($app) === 3){
            return "{$app[2]}-{$app[1]}-{$app[0]}";
        }
        return $date;
    }
    
    public function setDateFormat($format = 'YYYY-MM-DD')
    {
        $this->execCommand("ALTER SESSION SET NLS_DATE_FORMAT = '{$format}'");
    }
/*End class*/
}
