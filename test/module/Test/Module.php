<?php
/**
 * Created by Gary Hockin.
 * Date: 18/12/14
 * @GeeH
 */

namespace Test;


use Test\Controller\TestController;
use Zend\Mvc\Controller\ControllerManager;
use Zend\Stdlib\Hydrator\ClassMethods;

class Module
{


    public function getConfig()
    {
        return [
            'router' => [
                'routes' => [
                    'test-route' => array(
                        'type' => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route'    => '/test',
                            'defaults' => array(
                                'controller' => 'Test\Controller\Test',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                ],
            ],
        ];
    }

    public function getServiceConfig()
    {
        return [
            'invokables' => [
                ClassMethods::class => ClassMethods::class,
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                TestController::class => function(ControllerManager $controllerManager)
                {
                    return new TestController($controllerManager->getServiceLocator()->get(ClassMethods::class));
                }
            ],
        ];
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}