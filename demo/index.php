<?php
include('../autoload.php');

use Osynapsy\Core\Base;

$base = new Base();
$kernel = $base->singleton('kernel');
var_dump($kernel);
?>
<html>
    <body>
        <form>
            <input id="test"/>
        </form>
    </body>
</html>