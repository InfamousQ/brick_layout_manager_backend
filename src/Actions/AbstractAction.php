<?php
/**
 * Created by PhpStorm.
 * User: tuomas
 * Date: 17.12.2018
 * Time: 23:59
 */

namespace InfamousQ\LManager\Actions;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class AbstractAction {

	/** @var ContainerInterface $container */
	protected $container;

	public function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	abstract public function __invoke(Request $request, Response $response);

}