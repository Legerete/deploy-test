<?php
	namespace App\WebSocket;

	use Nette\Application\Application;
	use Ratchet\MessageComponentInterface;
	use Ratchet\ConnectionInterface;

	class App implements MessageComponentInterface {
		protected $clients;

		/**
		 * @var ConnectionInterface
		 */
		private $conn;

		/**
		 * @var Application
		 */
		private $application;

		public function __construct(Application $application) {
			$this->clients = new \SplObjectStorage;
			$this->application = $application;
		}

		public function onOpen(ConnectionInterface $conn) {
			// Store the new connection to send messages to later
			$this->clients->attach($conn);
			$this->conn = $conn;

			echo "New connection! ({$conn->resourceId})\n";
		}

		public function onMessage(ConnectionInterface $from, $msg) {

			ob_start();
				$this->application->run();
			$result = ob_flush();


			$numRecv = count($this->clients) - 1;
			echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
				, $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

			foreach ($this->clients as $client) {
				if ($from !== $client) {
					// The sender is not the receiver, send to each client connected
					$client->send($result);
				} else {
					$client->send($result);
				}
			}
		}

		public function onClose(ConnectionInterface $conn) {
			// The connection is closed, remove it, as we can no longer send it messages
			$this->clients->detach($conn);

			echo "Connection {$conn->resourceId} has disconnected\n";
		}

		public function onError(ConnectionInterface $conn, \Exception $e) {
			echo "An error has occurred: {$e->getMessage()}\n";

			$conn->close();
		}
	}