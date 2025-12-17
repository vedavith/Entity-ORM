<?php

namespace EntityForge\EntityGenerator;

use EntityForge\EntityGenerator\ModelMeta\AbstractModelMeta;
use EntityForge\Core\ModelGenerator as Generator;
use EntityForge\Core\RepositoryGenerator as RepoGenerator;

class GenerateModel extends AbstractModelMeta {
    private object $metaObject;
    private $logger;

    /**
     *  Constructor
     */
    public function __construct() {
        // Delay driver instantiation until needed (avoid requiring PDO driver when not creating tables)
    }

    /**
     * __builder - Takes in builder data object and generates the table and POCO class for given model
     *
     * @param object $builderMeta
     * @param bool $table
     * @return boolean|\Exception
     */
    public final function __builder(object $builderMeta, bool $table = false) : bool|\Exception {
        try {
            // Table creation removed; only generate model and repository files
            return $this->generateModelFile($builderMeta);
        } catch (\Exception $ex) {
            $this->logger[__FUNCTION__] = $ex->getMessage();
            return false;
        }
    }

    // Table generation has been removed.

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
    // Table generation removed; driver usage eliminated.

    /**
     * @param object $builderMeta
     * @return bool
     */
    private function generateModelFile(object $builderMeta) : bool {
        try {
            $modelOk = (new Generator())->generateModel($builderMeta);
            $repoOk = (new RepoGenerator())->generateRepository($builderMeta);
            return (bool)($modelOk && $repoOk);
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