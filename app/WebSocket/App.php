<?php
	namespace App\WebSocket;

	use App\WebSocket\Request\WebSocketRequestFactory;
	use Legerete\Websocket\Application\Application;
	use Nette\DI\Container;
	use Nette\Http\UrlScript;
	use Nette\Utils\Arrays;
	use Nette\Utils\Json;
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

		private $requestFactory;

		private $container;

		private $message;

		public function __construct(Application $application, WebSocketRequestFactory $requestFactory, Container $container) {
			$this->clients = new \SplObjectStorage;
			$this->application = $application;
			$this->requestFactory = $requestFactory;
			$this->container = $container;
		}

		public function onOpen(ConnectionInterface $conn) {
			// Store the new connection to send messages to later
			$this->clients->attach($conn);
			$this->conn = $conn;

			echo "New connection! ({$conn->resourceId})\n";
		}

		public function onMessage(ConnectionInterface $from, $message) {
			$this->message = $message;

			$message = Json::decode($this->message, Json::FORCE_ARRAY);
			$request = Arrays::get($message, 'request', 'http://localhost:8005/');

			ob_start();
				try {
					$httpRequest = $this->requestFactory->setUrl($request)->createHttpRequest();
					$this->application->setHttpRequest($httpRequest);
					$this->application->run();
				} catch (\Exception $e) {
					\Tracy\Debugger::log($e);
				}
			$result = ob_get_clean();

			$numRecv = count($this->clients) - 1;
			echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
				, $from->resourceId, $message, $numRecv, $numRecv == 1 ? '' : 's');

			foreach ($this->clients as $client) {
				if ($from !== $client) {
					// The sender is not the receiver, send to each client connected
					$client->send($from->session->getId());
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