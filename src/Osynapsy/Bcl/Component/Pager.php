<?php
namespace Osynapsy\Bcl\Component;

use Osynapsy\Ocl\Component\Component;
use Osynapsy\Ocl\Component\HiddenBox;
use Osynapsy\Core\Lib\Tag;
/**
 * Description of Pager
 *
 * @author Pietro Celeste
 */
class Pager extends Component
{
    private $columns = array();
    protected $data = array();
    private $db;
    private $filters = array();
    private $loaded = false;
    private $par;
    private $sql;  
    private $orderBy = null;
    private $page = array(
        'dimension' => 10,
        'total' => 1,
        'current' => 1
    ); //Dimension of the pag in row;
    private $total = array(
        'rows' => 0        
    );
    //put your code here
    public function __construct($id, $dim = 10, $tag = 'div', $infiniteContainer = false)
    {        
        parent::__construct($tag, $id);
        if (!empty($infiniteContainer)) {
            $this->setInfiniteScroll($infiniteContainer);
        }
        $this->requireJs('/__assets/osynapsy/Bcl/Pager/script.js');
        $this->att('class','BclPager',true);
        if ($tag == 'form') {
            $this->att('method','post');
        }
        if ($dim) {
            $this->page['dimension'] = $dim;
        }
    }
    
    public function __build_extra__()
    {
        if (!$loaded) {
            $this->loadData;
        }
        $this->add(new HiddenBox($this->id));
        $ul = $this->add(new Tag('ul'));
        $ul->att('class','pagination');
        $liFirst = $ul->add(new Tag('li'));
        if ($this->page['current'] < 2) {
            $liFirst->att('class','disabled');
        }
        $liFirst->add(new Tag('a'))
                ->att('data-value','first')
                ->att('href','#')
                ->add('&laquo;');
        $dim = min(7, $this->page['total']);
        $app = floor($dim / 2);
        $pageMin = max(1, $this->page['current'] - $app);
        $pageMax = max($dim, min($this->page['current'] + $app, $this->page['total']));
        $pageMin = min($pageMin,$this->page['total'] - $dim + 1);
        for ($i = $pageMin; $i <= $pageMax; $i++) {
            $liCurrent = $ul->add(new Tag('li'));
            if ($i == $this->page['current']) {
                $liCurrent->att('class','active');
            }
            $liCurrent->att('class','text-center',true)
                      ->add(new Tag('a'))
                      ->att('data-value',$i)
                      ->att('href','#')
                      ->add($i);
        }
        $liLast = $ul->add(new Tag('li'));
        if ($this->page['current'] >= $this->page['total']) {
            $liLast->att('class','disabled');
        }
        $liLast->add(new Tag('a'))
               ->att('href','#')
               ->att('data-value','last')
               ->add('&raquo;');
    }
    
    public function addFilter($field, $value = null)
    {
        $this->filters[$field] = $value;
    }
    
    private function buildMySqlQuery($where)
    {
        $sql = "SELECT a.* FROM ({$this->sql}) a {$where} ";
        if (!empty($_REQUEST[$this->id.'_order'])) {
            $sql .= ' ORDER BY '.str_replace(array('][','[',']'),array(',','',''),$_REQUEST[$this->id.'_order']);
        } elseif ($this->orderBy) {
            $sql .= "\nORDER BY {$this->orderBy}";
        }
        if (empty($this->page['dimension'])) {
            return $sql;
        }
        $startFrom = ($this->page['current'] - 1) * $this->page['dimension'];
        $startFrom = max(0, $startFrom);
        
        $sql .= "\nLIMIT ".$startFrom." , ".$this->page['dimension'];
        return $sql;
    }
    
    private function buildPgSqlQuery($where)
    {
        $sql = "SELECT a.* FROM ({$this->sql}) a {$where} ";
        if (!empty($_REQUEST[$this->id.'_order'])) {
            $sql .= ' ORDER BY '.str_replace(array('][','[',']'),array(',','',''),$_REQUEST[$this->id.'_order']);
        } elseif ($this->orderBy) {
            $sql .= "\nORDER BY {$this->orderBy}";
        }
        if (empty($this->page['dimension'])) {
            return $sql;
        }
        $startFrom = ($this->page['current'] - 1) * $this->page['dimension'];
        $startFrom = max(0, $startFrom);
        $sql .= "\nLIMIT ".$this->page['dimension']." OFFSET ".$startFrom;              
        return $sql;
    }
    
    private function buildOracleQuery($where)
    {
        $sql = "SELECT a.*
                FROM (
                    SELECT b.*,rownum as \"_rnum\"
                    FROM (
                        SELECT a.*
                        FROM ($this->sql) a
                        ".(empty($where) ? '' : $where)."
                        ".(!empty($_REQUEST[$this->id.'_order']) ? ' ORDER BY '.str_replace(array('][','[',']'),array(',','',''),$_REQUEST[$this->id.'_order']) : '')."
                    ) b
                ) a ";
        if (empty($this->page['dimension'])) {
            return $sql;
        }
        $startFrom = (($this->page['current'] - 1) * $this->page['dimension']) + 1 ;
        $endTo = ($this->page['current'] * $this->page['dimension']);
        $sql .=  "WHERE \"_rnum\" BETWEEN $startFrom AND $endTo";
        return $sql;
    }
    
    private function buildFilter()
    {
        if (empty($this->filters)) {
            return;
        }
        $filter = array();
        $i = 0;
        foreach ($this->filters as $field => $value) {
            if (is_null($value)) {
                $filter[] = $field;
                continue;
            }
            $filter[] = "$field = ".($this->db->getType() == 'oracle' ? ':'.$i : '?');
            $this->par[] = $value;
            $i++;
        }       
        return " WHERE " .implode(' AND ',$filter);        
    }

    private function calcPage()
    {
        $this->page['current'] = max(1, $_REQUEST[$this->id]+0);
        if ($this->total['rows'] == 0 || empty($this->page['dimension'])) {
            return;
        }
        $this->page['total'] = ceil($this->total['rows'] / $this->page['dimension']);
        $this->att(
            'data-page-max',
            max($this->page['total'],1)
        );
        switch ($_REQUEST[$this->id]) {
            case 'first':
                $this->page['current'] = 1;
                break;
            case 'last' :
                $this->page['current'] = $this->page['total'];
                break;
            case 'min':
                if ($this->page['current'] > 1){
                    $this->page['current']--;
                }
                break;
            case 'min':
                if ($this->page['current'] < $this->page['total']) {
                    $this->page['current']++;
                }
                break;
            default:
                $this->page['current'] = min($this->page['current'], $this->page['total']);
                break;
        }
    }
    
    private function calcStatistics()
    {
        //Calcolo statistiche
        if (!$this->sqlStat) {
            return;
        }
        try {
            $sql_stat = Kernel::replaceVariable(str_replace('<[datasource-sql]>',$sql,$sql_stat).$whr);
            $stat = $this->db->execUnique($sql_stat,null,'ASSOC');
            if (!is_array($stat)) $stat = array($stat);
            $dstat = tag::create('div')->att('class',"osy-datagrid-stat");
            $tr = $dstat->add(tag::create('table'))->att('align','right')->add(tag::create('tr'));
            foreach ($stat as $k=>$v) {
                $v = ($v > 1000) ? number_format($v,2,',','.') : $v;
                $tr->add(Tag::create('td'))->add('&nbsp;');
                $tr->add(Tag::create('td'))->att('title',$k)->add($k);
                $tr->add(Tag::create('td'))->add($v);
            }
            $this->__par['div-stat'] = $dstat;
        } catch(\Exception $e) {
                $this->par('error-in-sql-stat','<pre>'.$sql_stat."\n".$e->getMessage().'</pre>');
        }
    }
    
    public function loadData()
    {        
        if (empty($this->sql)) {
            return array();
        }
        $where = $this->buildFilter();
      
        $count = "SELECT COUNT(*) FROM (\n{$this->sql}\n) a " . $where;
          
        try {                     
            $this->total['rows'] = $this->db->execUnique($count, $this->par);
            $this->att('data-total-rows',$this->total['rows']);
        } catch(\Exception $e) {
            echo $this->errors[] = '<pre>'.$count."\n".$e->getMessage().'</pre>';
            return array();
        }
        
        $this->calcPage();
        
        switch ($this->db->getType()) {
            case 'oracle':
                $sql = $this->buildOracleQuery($where);
                break;
            case 'pgsql':
                $sql = $this->buildPgSqlQuery($where);
                break;
            default:
                $sql = $this->buildMySqlQuery($where);
                break;
        }
        //Eseguo la query        
        try {
            $this->data = $this->db->execQuery($sql, $this->par, 'ASSOC');
        } catch (\Exception $e) {
            die($sql.$e->getMessage());
        }
        //die(print_r($this->data,true));
        //Salvo le colonne in un option
        $this->columns = $this->db->getColumns();
        return empty($this->data) ? array() : $this->data;
    }
    
    public function setSql($db, $cmd, array $par = array())
    {
        $this->db = $db;
        $this->sql = $cmd;
        $this->par = $par;
        return $this;
    }
    
    public function setOrder($field)
    {
        $this->orderBy = $field;
    }
    
    public function setInfiniteScroll($container)
    {
        $this->requireJs('/__assets/osynapsy/Bcl/Pager/imagesloaded.js');
        $this->requireJs('/__assets/osynapsy/Bcl/Pager/wookmark.js');
        $this->att('class','infinitescroll',true)->att('style','display: none');
        if ($container[0] != '#' ||  $container[0] != '#') {
            $container = '#'.$container;
        }
        return $this->att('data-container',$container);
    }
}
