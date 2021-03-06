<?php
/**
 * Created by Gary Hockin.
 * Date: 18/12/14
 * @GeeH
 */

namespace Test\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\View\Model\ViewModel;

class TestController extends AbstractActionController
{

    /**
     * @var HydratorInterface
     */
    protected $hydrator;
    /**
     * @var HydratorInterface
     */
    protected $anotherHydrator;

    public function __construct(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
        $this->anotherHydrator = $hydrator;
    }

    public function indexAction()
    {
        $this->hydrator->hydrate(['hello' => 'mum'], new \stdClass());
        return new ViewModel(['jimmy' => 'nail', 'colin' => 'pascoe']);
    }

    /**
     * @param HydratorInterface $anotherHydrator
     */
    public function setAnotherHydrator($anotherHydrator)
    {
        $this->anotherHydrator = $anotherHydrator;
    }
}
