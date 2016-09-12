<?php
	namespace App\WebSocket;

	use Legerete\Websocket\Request\WebSocketRequestFactory;
	use Legerete\Websocket\Application\Application;
	use Nette\DI\Container;
	use Nette\Utils\Arrays;
	use Nette\Utils\Json;
	use Ratchet\MessageComponentInterface;
	use Ratchet\ConnectionInterface;
	use Tracy\ILogger;

	class App implements MessageComponentInterface {

		const WEBSOCKET_INFO_FILE = 'WebSocketInfo';

		protected $clients;

		/**
		 * @var ConnectionInterface
		 */
		private $conn;

		/**
		 * @var Application
		 */
		private $application;

		/**
		 * @var WebSocketRequestFactory
		 */
		private $requestFactory;

		/**
		 * @var Container
		 */
		private $container;

		/**
		 * @var string
		 */
		private $message;

		/**
		 * @var ILogger
		 */
		private $logger;

		public function __construct(Application $application, WebSocketRequestFactory $requestFactory, Container $container, ILogger $logger) {
			$this->clients = new \SplObjectStorage;
			$this->application = $application;
			$this->requestFactory = $requestFactory;
			$this->container = $container;
			$this->logger = $logger;
		}

		public function onOpen(ConnectionInterface $conn) {
			// Store the new connection to send messages to later
			$this->clients->attach($conn);
			$this->conn = $conn;

			$this->logger->log("New connection! ({$conn->resourceId})\n", self::WEBSOCKET_INFO_FILE);
		}

		public function onMessage(ConnectionInterface $from, $message) {
			$this->message = $message;

			$message = Json::decode($this->message, Json::FORCE_ARRAY);
			$request = Arrays::get($message, 'request', 'http://localhost:8005/');
			ob_start();
				try {
					session_id($from->session->getId());
					$httpRequest = $this->requestFactory->setUrl($request)->createHttpRequest();
					$this->application->setHttpRequest($httpRequest);
					$this->application->run();
					session_id(FALSE);
				} catch (\Exception $e) {
					$this->logger->log($e);
				}
			$result = ob_get_clean();

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
			$this->logger->log("Connection {$conn->resourceId} has disconnected\n", self::WEBSOCKET_INFO_FILE);
		}

		public function onError(ConnectionInterface $conn, \Exception $e) {
			$this->logger->log("An error has occurred: {$e->getMessage()}\n", self::WEBSOCKET_INFO_FILE);
			$conn->close();
		}

	}