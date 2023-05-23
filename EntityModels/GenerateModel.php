<?php 
namespace EntityORM\EntityModels;

require_once '../vendor/autoload.php';
require_once '../EntityConnector/EntityDriver.php';
require_once 'IModelMeta.php';

use EntityORM\EntityConnector\EntityDriver as Driver;
use EntityORM\EntityModels\ModelMeta\IModelMeta;
class GenerateModel extends IModelMeta {
    private $driverObject;
    public function __construct() {
        $this->driverObject = new Driver();
    }

    protected function validateModel(object $yamlObject) : bool {
        return true;
    }

    protected function buildTableFromMeta(object $yamlObject) : bool | \Exception {
        return true;
    }

    protected function buildModelFromMeta(object $yamlObject) : bool | \Exception {
        return true;
    }

    public final function __builder(object $yamlObject) : bool|\Exception {
        if(!$this->validateModel($yamlObject)) {
            return new \Exception("Validation Failed");
        }

        $modelFiber = new \Fiber( function($begin) use($yamlObject) {
            if($begin) {
                return $this->buildModelFromMeta($yamlObject);
            }
            \Fiber::suspend($begin);
        });

        $modelFiber->start(begin: false);
        if(!$modelFiber->resume($this->buildModelFromMeta($yamlObject))){
            return new \Exception("Model Building Failed");
        }

    }

}