<?php
/**
 * Created by Gary Hockin.
 * Date: 18/12/14
 * @GeeH
 */

namespace UglyTestingTest;


use Test\Controller\TestController;
use UglyTestingTest\Asset\SampleControllerTestFile;
use Zend\View\Model\ViewModel;

class AbstractControllerTestCaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SampleControllerTestFile
     */
    protected $controllerTest;

    public function setUp()
    {
        $this->controllerTest         = new SampleControllerTestFile();
        $this->controllerTest->config = __DIR__ . '/../config/testing.config.php';
    }

    public function testController()
    {
        $this->controllerTest->givenTestsController(TestController::class);

        $mock = $this->getMock(ClassMethods::class, ['hydrate']);
        $mock->expects($this->once())
            ->method('hydrate');

        $this->controllerTest->givenMockedClass('hydrator', $mock);
        $this->controllerTest->givenMockedClass('anotherHydrator', $mock);
        $this->controllerTest->givenUrl('/test')
            ->givenQueryParameters(['jimmy' => 'nail'])
            ->shouldRouteTo('test-route')
            ->shouldRunAction('index', 'test-route')
            ->shouldReturnA(ViewModel::class)
            ->shouldHaveViewVariables(['jimmy' => 'nail', 'colin' => 'pascoe']);
    }

    public function testControllerWithoutControllerExcepts()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Controller class needs to be set before specifying givens'
        );

        $this->controllerTest->givenUrl('/home');
    }
}
