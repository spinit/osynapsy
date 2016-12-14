<?php
namespace Osynapsy;

/**
 * Permette la gestione di oggetti che devono essere unici nel sistema.
 * Attraverso get e set è possibile associare ad un oggetto un nome mnemonico in modo
 * da poterlo riprendere in varie parti del codice.
 *
 * Di corredo sono implementati i metodi che garantiscono un valore di inizializzazione
 * per un particolare "nome"
 */
class Singleton
{
    private static $instances = array();

    public static function get($name)
    {
        return @self::$instances[$name];
    }
    
    public static function set($name, $value)
    {
        self::$instances[$name] = $value;
        return $value;
    }
    
    /**
     * Method Factory per recuperare l'oggetto kernel
     */
    public static function kernel($obj = false)
    {
        if ($obj) {
            self::set('kernel', $obj);
        }
        $kernel = self::get('kernel');
        if (!$kernel) {
            $kernel = self::set('kernel', new \Osynapsy\Core\Kernel());
        }
        return $kernel;
    }
}
