<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class SteinScherePapier implements MessageComponentInterface 
{
	private $clients;
	private $last_c;
	private $last_m;
	
	public function __construct() {
		$this->clients = new \SplObjectStorage();
	}
	
	public function onOpen(ConnectionInterface $conn) {
		$this->clients->attach($conn, 0);
		$this->broadcast($conn, 'login', true);
	}
	
	public function onMessage(ConnectionInterface $from, $msg) {
		if (empty($this->last_c)) {
			$p1_id = spl_object_hash($from);
			$p1 = $msg;
			print "\nwahl P1 $p1_id : $p1";
			$this->last_c = $from;
			$this->last_m = $msg;
		} else { 
			$p2_id = spl_object_hash($from);
			$p2 = $msg;
			print "\nwahl P2 $p2_id : $p2";
			
			$p1_id = spl_object_hash($this->last_c);
			$p2_id = spl_object_hash($from);
			$p1 = $this->last_m;
			$p2 = $msg;
			//print "\nplay P1 $p1_id : $p1  VS  $p2_id : $p2";		
			print "\nplay $p1  VS  $p2";		
			$score = $this->play($this->last_m, $msg);
			print " -> score $score";
			if (isset($this->clients[$this->last_c])) {
				$this->clients[$this->last_c] += $score;
			} else {
				$this->clients[$this->last_c] = $score;
			}
			$this->clients[$from] -= $score;
			$this->broadcast($from, 'turn', array(
						'score' => $this->clients[$from], 
						'play' => $msg
					));
			$this->broadcast($this->last_c, 'turn', array(
						'score' => $this->clients[$this->last_c], 
						'play' => $this->last_m
					));
			unset($this->last_c);
		}
	}
	
	public function onClose(ConnectionInterface $conn) {
		$this->clients->detach($conn);
		$this->broadcast($conn, 'logout', true);
	}
	
	public function onError(ConnectionInterface $conn, \Exception $e) {
		echo "Fehler: {$e->getMessage()}\n";
		$conn->close();
	}
	
	public function broadcast(ConnectionInterface $from, $type, $msg) {
		$id = spl_object_hash($from);
		$msg = json_encode(array('id' => $id, $type => $msg));
		print "\nbroadcast $msg";
		foreach ($this->clients as $client) {
			$client->send($msg);
		}
	}
	
	private function play($p1, $p2) {
		if ($p1 == $p2) {
			return 0;
		}
		/* 
		Schere schneidet Papier, Papier bedeckt Stein, Stein zerquetscht Echse, 
		Echse vergiftet Spock. Spock zertrümmert Schere, Schere köpft Echse, 
		Echse frisst Papier. Papier widerlegt Spock, Spock verdampft Stein. 
		Und wie gewöhnlich – Stein schleift Schere.
		*/
		switch ($p1) {
			case 'Schere': return ($p2 == 'Papier' || $p2 == 'Echse') ? 1 : -1;
			case 'Stein': return ($p2 == 'Echse' || $p2 == 'Schere') ? 1 : -1;
			case 'Papier': return ($p2 == 'Stein' || $p2 == 'Spock') ? 1 : -1;
			case 'Echse': return ($p2 == 'Spock' || $p2 == 'Papier') ? 1 : -1;
			case 'Spock': return ($p2 == 'Stein' || $p2 == 'Schere') ? 1 : -1;
		}
	}
}
?>