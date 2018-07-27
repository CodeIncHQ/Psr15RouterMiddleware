<?php
//
// +---------------------------------------------------------------------+
// | CODE INC. SOURCE CODE                                               |
// +---------------------------------------------------------------------+
// | Copyright (c) 2018 - Code Inc. SAS - All Rights Reserved.           |
// | Visit https://www.codeinc.fr for more information about licensing.  |
// +---------------------------------------------------------------------+
// | NOTICE:  All information contained herein is, and remains the       |
// | property of Code Inc. SAS. The intellectual and technical concepts  |
// | contained herein are proprietary to Code Inc. SAS are protected by  |
// | trade secret or copyright law. Dissemination of this information or |
// | reproduction of this material is strictly forbidden unless prior    |
// | written permission is obtained from Code Inc. SAS.                  |
// +---------------------------------------------------------------------+
//
// Author:   Joan Fabrégat <joan@codeinc.fr>
// Date:     03/07/2018
// Time:     13:02
// Project:  Psr15RouterMiddleware
//
declare(strict_types=1);
namespace CodeInc\Psr15RouterMiddleware;
use CodeInc\Psr15RouterMiddleware\Exceptions\ControllerInstantiatingException;
use CodeInc\Psr15RouterMiddleware\Exceptions\ControllerProcessingException;
use CodeInc\Psr15RouterMiddleware\Exceptions\NotAControllerException;
use CodeInc\Psr7Responses\NotFoundResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;


/**
 * Class RouterMiddleware
 *
 * @package CodeInc\Psr15RouterMiddleware
 * @author Joan Fabrégat <joan@codeinc.fr>
 */
class RouterMiddleware implements MiddlewareInterface
{
    /**
     * @var string[]
     */
    private $controllersClass;

    /**
     * @var string|null
     */
    private $notFoundControllerClass;

    /**
     * @var bool
     */
    private $sendNotFoundResponse;

    /**
     * RouterMiddleware constructor.
     *
     * @param bool $sendNotFoundResponse Defines if the router must send a not found response (either from a not found
     *     controller or a NotFoundResponse object) or should call the next request handler.
     */
    public function __construct(bool $sendNotFoundResponse = true)
    {
        $this->sendNotFoundResponse = $sendNotFoundResponse;
    }

    /**
     * @param string $controllerClass
     * @throws NotAControllerException
     */
    public function registerController(string $controllerClass):void
    {
        if (!is_subclass_of($controllerClass, ControllerInterface::class)) {
            throw new NotAControllerException($controllerClass);
        }
        /** @var $controllerClass ControllerInterface */
        /** @noinspection PhpStrictTypeCheckingInspection */
        $this->mapPathToController($controllerClass::getUriPath(), $controllerClass);
    }

    /**
     * Maps a path to a controller.
     *
     * @param string $path
     * @param string $controllerClass
     */
    protected function mapPathToController(string $path, string $controllerClass):void
    {
        $this->controllersClass[$path] = $controllerClass;
    }

    /**
     * Sets the controller called if no other controller is found to handle the request.
     *
     * @param string $notFoundControllerClass
     * @param bool $mapPath
     */
    public function setNotFoundController(string $notFoundControllerClass, bool $mapPath = true):void
    {
        if (!is_subclass_of($notFoundControllerClass, ControllerInterface::class)) {
            throw new NotAControllerException($notFoundControllerClass);
        }
        /** @var $notFoundControllerClass ControllerInterface */
        $this->notFoundControllerClass = $notFoundControllerClass;
        if ($mapPath) {
            $this->mapPathToController($notFoundControllerClass::getUriPath(), $notFoundControllerClass);
        }
    }

    /**
     * @inheritdoc
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler):ResponseInterface
    {
        // processes the request using the controller
        $controllerClass = $this->controllersClass[$request->getUri()->getPath()] ?? null;
        if (!$controllerClass) {
            return $this->executeController(
                $this->instantiateController($controllerClass, $request)
            );
        }

        // if not controller is found -> sends a not found response
        elseif ($this->sendNotFoundResponse) {
            if ($this->notFoundControllerClass) {
                return $this->executeController(
                    $this->instantiateController($this->notFoundControllerClass, $request)
                );
            }
            else {
                return new NotFoundResponse();
            }
        }

        // else if not found responses are disabled -> calls the next request handler
        else {
            return $handler->handle($request);
        }
    }

    /**
     * Instantiates a controller.
     *
     * @param string $controllerClass
     * @param ServerRequestInterface $request
     * @return ControllerInterface
     */
    protected function instantiateController(string $controllerClass,
        ServerRequestInterface $request):ControllerInterface
    {
        try {
            return new $controllerClass($request);
        }
        catch (Throwable $exception) {
            throw new ControllerInstantiatingException($controllerClass, 0, $exception);
        }
    }

    /**
     * Executes a controller.
     *
     * @param ControllerInterface $controller
     * @return ResponseInterface
     */
    protected function executeController(ControllerInterface $controller):ResponseInterface
    {
        try {
            return $controller->getResponse();
        }
        catch (Throwable $exception) {
            throw new ControllerProcessingException($controller, 0, $exception);
        }
    }
}