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
     * @param string $controllerClass
     * @throws NotAControllerException
     */
    public function registerControllerClass(string $controllerClass):void
    {
        if (is_subclass_of($controllerClass, ControllerInterface::class)) {
            throw new NotAControllerException($controllerClass);
        }
        /** @var $controllerClass ControllerInterface */
        $this->controllersClass[$controllerClass::getUriPath()] = $controllerClass;
    }

    /**
     * @param string $notFoundControllerClass
     * @throws NotAControllerException
     */
    public function setNotFoundControllerClass(string $notFoundControllerClass):void
    {
        if (is_subclass_of($notFoundControllerClass, ControllerInterface::class)) {
            throw new NotAControllerException($notFoundControllerClass);
        }
        $this->notFoundControllerClass = $notFoundControllerClass;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler):ResponseInterface
    {
        $controllerClass = $this->controllersClass[$request->getUri()->getPath()] ?? null;
        if ($controllerClass) {
            return $this->processController($controllerClass, $request);
        }
        elseif ($this->notFoundControllerClass) {
            return $this->processController($this->notFoundControllerClass, $request);
        }
        else {
            return new NotFoundResponse();
        }
    }

    /**
     * @param string $controllerClass
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws ControllerProcessingException
     */
    private function processController(string $controllerClass, ServerRequestInterface $request):ResponseInterface
    {
        try {
            /** @var ControllerInterface $controller */
            $controller = new $controllerClass($request);
            return $controller->process();
        }
        catch (Throwable $exception) {
            throw new ControllerProcessingException($controllerClass, 0, $exception);
        }
    }
}