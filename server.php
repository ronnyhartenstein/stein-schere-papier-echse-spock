<?php
/**
 * Start eines Ratchet Servers
 */

require 'vendor/autoload.php';
require 'SteinScherePapier.class.php';

use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(new WsServer(new SteinScherePapier()), 8000);
$server->run();

?>