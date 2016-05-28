<?php
namespace Osynapsy\Ocl\Component;

class VariableBox extends Component
{
    public function __construct($name)
    {
        parent::__construct('dummy',$name);
    }
    
    public function __build_extra__()
    {
        $sql = $this->get_par('datasource-sql');
        if (empty($sql)) {
            throw new \Exception('[ERROR] - variable box '.$this->id.' - query builder assente');
        }
        $sql = kkernel::ReplaceVariable($sql);
        $sql = kkernel::parse_string($sql);
        list($typ,$sql) = kkernel::$dba->exec_unique($sql);
        switch ($typ){
            case 'CMB':
                $sql = kkernel::ReplaceVariable($sql);
                $this->add(new combo_box($this->id))
                     ->att('label',$this->label)
                    ->par('datasource-sql',$sql);//Setto la risorsa per popolare la combo e la connessione al DB necessaria ad effettuare le query.
                break;
            case 'TAR':
                $this->add(new text_area($this->id))->att('style','width: 95%;')->att('rows','20');
                break;
            default :
                $this->add(new text_box($this->id))->att('style','width: 95%');
                break;
        }
    }
}