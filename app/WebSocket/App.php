<?php
	/**
	 * @copyright   Copyright (c) 2016 legerete.cz <hello@legerete.cz>
	 * @author      Petr Besir Horáček <sirbesir@gmail.com>
	 * @package     App
	 */

	namespace App\WebSocket;

	use Legerete\WebSocket\Message\ReceivedMessage;
	use Legerete\WebSocket\Message\ReceivedMessageFactory;
	use Legerete\WebSocket\Request\WebSocketRequestFactory;
	use Legerete\WebSocket\Application\Application;
	use Nette\DI\Container;
	use Nette\Utils\Arrays;
	use Nette\Utils\Json;
	use Ratchet\MessageComponentInterface;
	use Ratchet\ConnectionInterface;
	use Tracy\ILogger;

	class App implements MessageComponentInterface {

		const WEBSOCKET_INFO_FILE = 'WebSocketInfo';

		/**
		 * @var \SplObjectStorage
		 */
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
		 * @var ReceivedMessage
		 */
		private $parsedMessage;

		/**
		 * @var ILogger
		 */
		private $logger;

		/**
		 * @var ReceivedMessageFactory
		 */
		private $receivedMessageFactory;

		/**
		 * App constructor.
		 *
		 * @param Application $application
		 * @param WebSocketRequestFactory $requestFactory
		 * @param Container $container
		 * @param ILogger $logger
		 */
		public function __construct(
			Application $application,
			WebSocketRequestFactory $requestFactory,
			Container $container,
			ILogger $logger,
			ReceivedMessageFactory $receivedMessageFactory
		) {
			$this->clients = new \SplObjectStorage;
			$this->application = $application;
			$this->requestFactory = $requestFactory;
			$this->container = $container;
			$this->logger = $logger;
			$this->receivedMessageFactory = $receivedMessageFactory;
		}

		/**
		 * @param ConnectionInterface $conn
		 */
		public function onOpen(ConnectionInterface $conn) {
			/**
			 * Store the new connection to send messages to later
			 */
			$this->clients->attach($conn);
			$this->conn = $conn;

			$this->logger->log("New connection! ({$conn->resourceId})\n", self::WEBSOCKET_INFO_FILE);
		}

		/**
		 * Called when any message was received
		 *
		 * @param ConnectionInterface $from
		 * @param string $message
		 */
		public function onMessage(ConnectionInterface $from, $message) {
			$this->message = $message;

			/**
			 * Buffering some application output, any echo from application trigger http headers error
			 */
			ob_start();

				try {
					/**
					 * Set user session id
					 */
					session_id($from->session->getId());

					/**
					 * Inject httpRequest to Nette Application fork
					 */
					$this->application->setHttpRequest($this->createFakeHttpRequest());

					/**
					 * Run application
					 */
					$this->application->run();

					/**
					 * Set user session id, for sure, maybe not needed
					 */
					session_id(FALSE);
				} catch (\Exception $e) {
					$this->logger->log($e);
					echo Json::encode(['status' => 'error', 'code' => 501]);
				}

			$result = ob_get_clean();

			/**
			 * @type ConnectionInterface $client
			 */
			foreach ($this->clients as $client) {
				if ($from !== $client) {
					/**
					 * Send message from client to all other connections, for debug purpose only!
					 */
					$client->send($message);
				} else {
					$client->send($result);
				}
			}
		}

		/**
		 * The connection was closed
		 *
		 * @param ConnectionInterface $conn
		 */
		public function onClose(ConnectionInterface $conn) {
			$this->clients->detach($conn);
			$this->logger->log("Connection {$conn->resourceId} has disconnected\n", self::WEBSOCKET_INFO_FILE);
		}

		/**
		 * @param ConnectionInterface $conn
		 * @param \Exception $e
		 */
		public function onError(ConnectionInterface $conn, \Exception $e) {
			$this->logger->log("An error has occurred: {$e->getMessage()}\n", self::WEBSOCKET_INFO_FILE);
			$conn->close();
		}

		/**
		 * @return \Nette\Http\Request
		 */
		private function createFakeHttpRequest()
		{
			$this->requestFactory->setMessage($this->parseWebSocketMessage());
			return $this->requestFactory->createHttpRequest();
		}

		/**
		 * @return ReceivedMessage
		 */
		private function parseWebSocketMessage()
		{
			$this->parsedMessage = $this->receivedMessageFactory->create($this->message);
			return $this->parsedMessage;
		}

	}