<?php

namespace UglyTesting;

/**
 * Created by Gary Hockin.
 * Date: 17/12/14
 * @GeeH
 */
abstract class ControllerTest extends PHPUnit_Framework_TestCase
{
    protected $controllerName;
    protected $controllerClass;
    protected $serviceManager;
    protected $setUp = false;

    protected function createController()
    {
        if(!$this->setUp) {
            $this->setUpModule();
        }
    }


    public function controller($controllerName)
    {

        $this->controllerName = $controllerName;

    }

    private function setUpModule()
    {

    }

}