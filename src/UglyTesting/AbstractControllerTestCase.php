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
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Created by Gary Hockin.
 * Date: 17/12/14
 * @GeeH
 */
abstract class AbstractControllerTestCase extends TestCase
{
    /**
     * @var string
     */
    public $config = 'config/application.config.php';

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
     * Sets the controller name and checks the controller is able to be located
     *
     * @param  string $controllerName
     * @return $this
     */
    public function givenTestsController($controllerName)
    {
        $this->setUpServiceManager();
        $this->controllerName = $controllerName;
        $this->createController();

        return $this;
    }

    /**
     * Sets up the application that is needed to run the integration tests
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
     * @param  string $uri
     * @return $this
     */
    public function givenUrl($uri)
    {
        $request = new Request();
        $request->setUri($uri);
        $this->setRequest($request);

        return $this;
    }

    /**
     * Sets the request to the right places
     *
     * @param Request $request
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
     * Asserts that the returned ViewModel has the expected view variables set
     *
     * @param  array  $variables
     * @return $this
     */
    public function shouldHaveViewVariables(array $variables)
    {
        $this->assertEquals($this->model->getVariables(), $variables);

        return $this;
    }

    /**
     * Asserts that the returned ViewModelInterface is of the correct type (dispatches request)
     *
     * @param  string $modelType
     * @return $this
     */
    public function shouldReturnA($modelType)
    {
        $this->controllerClass->setEvent($this->application->getMvcEvent());
        $response = $this->controllerClass->dispatch($this->application->getRequest());

        $this->assertInstanceOf($modelType, $response);

        $this->model = $response;

        return $this;
    }

    /**
     * Asserts that the action that is resolved by router is correct
     *
     * @param  string $action
     * @return $this
     */
    public function shouldRunAction($action)
    {
        $this->assertEquals($action, $this->application->getMvcEvent()->getRouteMatch()->getParam('action'));

        return $this;

    }

    /**
     * Sets the query string parameters that would be sent with the Uri
     *
     * @param  array  $parameters
     * @return $this
     */
    public function givenQueryParameters(array $parameters)
    {
        /* @var Request $request */
        $request = $this->application->getRequest();
        $request->setQuery(new Parameters($parameters));

        return $this;
    }

    /**
     * Checks that the routed Uri resolves to the expected route name (routes request)
     *
     * @param  string $routeName
     * @return $this
     */
    public function shouldRouteTo($routeName)
    {
        $routerFactory = new RouterFactory();

        /* @var RouteStackInterface $router */
        $router = $routerFactory->createService($this->serviceManager);

        $mvcEvent = $this->application->getMvcEvent();
        $mvcEvent->setRouter($router);

        $match = $mvcEvent->getRouter()->match($this->application->getRequest());
        $mvcEvent->setRouteMatch($match);

        $this->assertEquals($match->getMatchedRouteName(), $routeName);

        return $this;
    }

    /**
     * Sets a property of the controller to a mock object either by setter, or by reflection
     *
     * @param  string $property
     * @param  object $mock
     * @return $this
     */
    public function givenMockedClass($property, $mock)
    {
        $setter = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $property)));
        if (method_exists($this->controllerClass, $setter)) {
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
