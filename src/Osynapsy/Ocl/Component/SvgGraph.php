<?php
namespace Osynpasy\Ocl\Component;

use SVGGraph as LibGraph;
use Osynapsy\Core\Request\Request;
use Osynapsy\Core\Lib\Dictionary;

/**
 * Description of SVGGraph
 *
 * @author Pietro Celeste <p.celeste@spinit.it>
 */
class SvgGraph
{
    protected $request;
    protected $db;
    protected $repo;
    //put your code here
    public function __construct(Request $request, $db)
    {
        $this->request = $request;
        $this->db = $db;
        $this->repo = new Dictionary(array(
            'width' => 320,
            'height' => 240,
            'type' => 'bar',
            'data' => array()
        ));
    }
    
    public function render()
    {
        ob_clean();
        $svg = new LibGraph(
            $this->get('width'),
            $this->get('height')
        );
        $svg->values(
            $this->get('data')
        );
        $svg->render(
            $this->get('type')
        );
        exit;
    }
    
    public function __call($name, $arguments)
    {
        switch ($name) {
            case 'get':
                $this->repo->get($arguments);
                break;
            default:
                $this->repo->set($name, $arguments);
                break;
        }
    }
}
