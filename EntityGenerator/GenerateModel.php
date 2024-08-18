<?php

namespace EntityORM\EntityGenerator;

require_once '../vendor/autoload.php';
// Load this By DI
require_once '../EntityConnector/EntityDriver.php';
require_once 'AbstractModelMeta.php';
// Load this BY DI
require_once '../Core/ModelGenerator.php';

use EntityORM\EntityConnector\EntityDriver as Driver;
use EntityORM\EntityGenerator\ModelMeta\AbstractModelMeta;
use EntityORM\Core\ModelGenerator as Generator;

class GenerateModel extends AbstractModelMeta {
    private $driverObject;
    private object $metaObject;
    private $logger;

    /**
     *  Constructor
     */
    public function __construct() {
        $this->driverObject = new Driver();
    }

    /**
     * __builder - Takes in builder data object and generates the table and POCO class for given model
     *
     * @param object $builderMeta
     * @param bool $table
     * @return boolean|\Exception
     */
    public final function __builder(object $builderMeta, bool $table = false) : bool|\Exception {
        $ok = false;
        try {
            $modelFiber = new \Fiber( function($begin) use($builderMeta, &$ok) {
                if($begin) {
                    $ok = $this->buildTableFromMeta($builderMeta);
                    \Fiber::suspend($begin);
                }
                \Fiber::suspend($begin);
            });

            $modelFiber->start(begin: $table);
            if(!$modelFiber->resume($this->generateModelFile($builderMeta))) {
                throw new \Exception("Model Building Failed");
            }
            return false;
        } catch (\Exception $ex) {
            $this->logger[__FUNCTION__] = $ex->getMessage();
            $ok = false;
        }
        return $ok;
    }

    /**
     * buildTableFromMeta - Builds table from YAML Object
     *
     * @param object $builderMeta
     * @return boolean|\Exception
     */
    protected function buildTableFromMeta(object $builderMeta) : bool | \Exception {
       return  $this->extractMeta($builderMeta)->generateTable();
    }

    /**
     * buildModelFromMeta - builds a POPO (Plain Old PHP Object) from YAML Object
     *
     *  Read json object and if table is true create a table in backend and create a poco file
     * @param object $builderMeta
     * @return boolean|\Exception
     */
    protected function buildModelFromMeta(object $builderMeta) : bool | \Exception {
       return true;
    }

    /**
     * @param object $builderMeta
     * @return self
     */
    private function extractMeta(object $builderMeta) : self {
        $meta = new \stdClass();
        $meta->table = $builderMeta->model;
        $columns = [];
        foreach ($builderMeta->fields as $field => $types) {
            $fieldMeta = null;
            if (!empty($types->type)) {
                $fieldMeta = $this->getDataTypeMapper($types->type);
            }

            if (!empty($types->maxLength)) {
                $fieldMeta .= "($types->maxLength)";
            }
            $columns[] =  $field." ".$fieldMeta;
        }
        $meta->columns = implode(",", $columns);
        $this->metaObject = $meta;
        return $this;
    }

    /**
     * @return bool
     */
    private function generateTable() : bool {
        try {
            return $this->driverObject->create($this->metaObject);
        } catch (\Exception $ex) {
            $this->setLogs([__FUNCTION__ => $ex->getMessage()]);
            return false;
        }
    }

    /**
     * @param object $builderMeta
     * @return bool
     */
    private function generateModelFile(object $builderMeta) : bool {
        try {
            return (new Generator())->generateModel($builderMeta);
        } catch (\Exception $ex) {
            $this->setLogs([__FUNCTION__ => $ex->getMessage()]);
            return false;
        }
    }

    /**
     * @param object $builderMeta
     * @return bool
     */
    protected function validateModel(object $builderMeta) : bool {
        return true;
    }

}