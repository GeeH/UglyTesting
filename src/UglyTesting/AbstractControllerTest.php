<?php

namespace UglyTesting;

use ReflectionObject;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Application;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\Mvc\Service\RouterFactory;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\DispatchableInterface;
use Zend\Stdlib\Parameters;
use Zend\View\Model\ModelInterface;

/**
 * Created by Gary Hockin.
 * Date: 17/12/14
 * @GeeH
 */
abstract class AbstractControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    protected $application;
    /**
     * @var string
     */
    protected $controllerName;
    /**
     * @var AbstractActionController
     */
    protected $controllerClass;
    /**
     * @var ServiceManager
     */
    protected $serviceManager;
    /**
     * @var ModelInterface
     */
    protected $model;
    /**
     * @var string
     */
    public $config = 'config/application.config.php';

    /**
     * Sets the controller name and checks the controller is able to be located
     *
     * @param $controllerName
     * @return $this
     */
    public function controller($controllerName)
    {
        $this->setUpServiceManager();
        $this->controllerName = $controllerName;
        $this->createController();
        return $this;
    }

    /**
     * Sets up the gubbins needed to run
     */
    protected function setUpServiceManager()
    {
        $this->serviceManager = Application::init(require $this->config)->getServiceManager();
        $this->serviceManager->setAllowOverride(true);
        $this->controllerManager = $this->serviceManager->get('ControllerManager');
        $this->application       = $this->serviceManager->get('Application');
    }

    /**
     * Creates a controller and checks it's of the right type
     */
    protected function createController()
    {
        $this->controllerClass = $this->controllerManager->get($this->controllerName);
        $this->assertInstanceOf(DispatchableInterface::class, $this->controllerClass);
    }

    /**
     * @param $uri
     * @return $this
     */
    public function route($uri)
    {
        $request = new Request();
        $request->setUri($uri);
        $this->setRequest($request);
        return $this;
    }

    /**
     * @param $request
     */
    protected function setRequest($request)
    {
        $this->serviceManager->setService('Request', $request);
        $refObject   = new ReflectionObject($this->application);
        $refProperty = $refObject->getProperty('request');
        $refProperty->setAccessible(true);
        $refProperty->setValue($this->application, $request);
        $refProperty->setAccessible(false);
    }

    /**
     * @param array $variables
     */
    public function withViewVariables(array $variables)
    {
        $this->assertEquals($this->model->getVariables(), $variables);
    }

    /**
     * @param $modelType
     * @return $this
     */
    public function andReturnA($modelType)
    {
        $this->controllerClass->setEvent($this->application->getMvcEvent());
        $response = $this->controllerClass->dispatch($this->application->getRequest());

        $this->assertInstanceOf($modelType, $response);

        $this->model = $response;

        return $this;
    }

    /**
     * @param $action
     * @param null $name
     * @return $this
     */
    public function shouldRunAction($action, $name = null)
    {
        $routerFactory = new RouterFactory();
        /** @var RouteStackInterface $router */
        $router = $routerFactory->createService($this->serviceManager);
        $this->application->getMvcEvent()->setRouter($router);

        $match = $this->application->getMvcEvent()->getRouter()->match($this->application->getRequest());
        $this->application->getMvcEvent()->setRouteMatch($match);

        $this->assertEquals($action, $this->application->getMvcEvent()->getRouteMatch()->getParam('action'));

        if (is_string($name)) {
            $this->assertEquals($name, $this->application->getMvcEvent()->getRouteMatch()->getMatchedRouteName());
        }
        return $this;

    }

    /**
     * @param array $parameters
     * @return $this
     */
    public function withQueryParameters(array $parameters)
    {
        /** @var Request $request */
        $request = $this->application->getRequest();
        $request->setQuery(new Parameters($parameters));
        return $this;
    }

    public function givenMockedClass($property, $mock)
    {
        $setter = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));
        if(method_exists($this->controllerClass, $setter)) {
            $this->controllerClass->$setter($mock);
            return $this;
        }

        $refObject   = new ReflectionObject($this->controllerClass);
        $refProperty = $refObject->getProperty($property);
        $refProperty->setAccessible(true);
        $refProperty->setValue($this->controllerClass, $mock);
        $refProperty->setAccessible(false);
        return $this;
    }

}