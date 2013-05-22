<?php

namespace PHPCI;

use b8\Config;
use b8\Http\Request;
use b8\Http\Response;
use b8\View;

class Controller extends \b8\Controller
{
	public function init() {}

	public function __construct(Config $config, Request $request, Response $response)
	{
		parent::__construct($config, $request, $response);

		$class = explode('\\', get_class($this));
		$this->className = substr(array_pop($class), 0, -10);
		$this->setControllerView();
	}

	protected function setControllerView()
	{
		if (View::exists($this->className)) {
			$this->controllerView = new View($this->className);
		} else {
			$this->controllerView = new View\UserView('{@content}');
		}
	}

	protected function setView($action)
	{
		if (View::exists($this->className . '/' . $action)) {
			$this->view = new View($this->className . '/' . $action);
		}
	}

	public function handleAction($action, $actionParams)
	{
		$this->setView($action);
		$response = parent::handleAction($action, $actionParams);

		if (is_string($response)) {
			$this->controllerView->content = $response;
		} elseif (isset($this->view)) {
			$this->controllerView->content = $this->view->render();
		}

		$this->response->setContent($this->controllerView->render());

		return $this->response;
	}
}