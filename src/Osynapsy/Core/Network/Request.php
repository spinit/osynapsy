<?php
namespace Osynapsy\Core\Network;

use Osynapsy\Core\Lib\Dictionary;

class Request extends Dictionary
{
    /**
     * Constructor.
     *
     * @param array           $query      The GET parameters
     * @param array           $request    The POST parameters
     * @param array           $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array           $cookies    The COOKIE parameters
     * @param array           $files      The FILES parameters
     * @param array           $server     The SERVER parameters
     * @param string|resource $content    The raw body data
     *
     * @api
     */
    public function __construct(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
    {
        $this->set('query', $query)
             ->set('request', $request)
             ->set('attributes', $attributes)
             ->set('cookies', $cookies)
             ->set('files', $files)
             ->set('server', $server)
             ->set('content', $content);
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
        $url .= $this->get('server.HTTP_HOST');
        $url .= $this->get('server.REQUEST_URI');
        $this->set('page.url',$url);
    }
}
