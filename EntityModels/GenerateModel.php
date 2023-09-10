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

    /**
     * buildTableFromMeta - Builds table from YAML Object
     *
     * @param object $builderMeta
     * @return boolean|Exception
     */
    protected function buildTableFromMeta(object $builderMeta) : bool | \Exception {
        var_dump(__FUNCTION__, $builderMeta);
        return true;
    }

    /**
     * buildModelFromMeta - builds a POPO (Plain Old PHP Object) from YAML Object
     *
     *  Read json object and if table is true create a table in backend and create a poco file
     * @param object $builderMeta
     * @return boolean|Exception
     */
    protected function buildModelFromMeta(object $builderMeta) : bool | \Exception {
        var_dump(__FUNCTION__, $builderMeta);
        return true;
    }

    public final function __builder(object $builderMeta, $table = false) : bool|\Exception {
        $ok = false;
        $modelFiber = new \Fiber( function($begin) use($builderMeta, &$ok) {
            if($begin) {
                $this->buildTableFromMeta($builderMeta);
                \Fiber::suspend($begin);
            }
            \Fiber::suspend($begin);

        });

        $modelFiber->start(begin: $table);

        if(!$modelFiber->resume($this->buildModelFromMeta($builderMeta))) {
            return new \Exception("Model Building Failed");
        }
        return false;
    }
}