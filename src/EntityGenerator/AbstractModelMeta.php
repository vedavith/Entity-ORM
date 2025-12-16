<?php
namespace EntityORM\EntityGenerator\ModelMeta;
use Exception;

abstract class AbstractModelMeta {
    private string $int;
    private string $float;
    private string $dateTime;
    private string $maxLength;
    private bool $autoIncrement;
    private readonly array $dataTypeMapper;
    private array $logs;

    //todo: Add getters and setters for keywords

    public function getDataTypeMapper($dataType) : string {
        $dataTypeMapper = [
            'int' => 'INTEGER',
            'integer' => 'INTEGER',
            'float' => 'DECIMAL',
            'char' => 'CHAR',
            'string' => 'VARCHAR',
            'datetime' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL',
            'date' => 'DATE'
        ];
        if (!array_key_exists($dataType, $dataTypeMapper)) {
            return 'VARCHAR';
        }
        return $dataTypeMapper[$dataType];
    }

    /**
     * Get the value of int
     */ 
    public function getInt() : string {
        return $this->int;
    }

    /**
     * Set the value of int
     *
     * @return  self
     */ 
    public function setInt(string $int) : self {
        $this->int = $int == "int" ? "INT" : null;
        return $this;
    }

    /**
     * Get the value of float
     */ 
    public function getFloat() : string {
        return $this->float;
    }

    /**
     * Set the value of float
     *
     * @return  self
     */ 
    public function setFloat(string $float) : self {
        $this->float = $float == "float" ? "DECIMAL" : null;
        return $this;
    }

    /**
     * Get the value of dateTime
     */ 
    public function getDateTime() : string{
        return $this->dateTime;
    }

    /**
     * Set the value of dateTime
     *
     * @return  self
     */ 
    public function setDateTime(string $dateTime) : self {
        $this->dateTime = $dateTime == "timestamp" ? 'TIMESTAMP NOT NULL DEFAULT CURRENT_DATE()' : null;
        return $this;
    }

    /**
     * Get the value of maxLength
     */ 
    public function getMaxLength() : string {
        return $this->maxLength;
    }

    /**
     * Set the value of maxLength
     *
     * @return  self
     */ 
    public function setMaxLength(int $maxLength) : self {
        $this->maxLength = !empty($maxLength) ? "($maxLength)" : null;

        return $this;
    }

    /**
     * Get the value of autoIncrement
     */ 
    public function getAutoIncrement() : string {
        return $this->autoIncrement;
    }

    /**
     * Set the value of autoIncrement
     *
     * @return  self
     */ 
    public function setAutoIncrement(bool $autoIncrement) : self {
        $this->autoIncrement = $autoIncrement == true ?: 'NOT NULL AUTO_INCREMENT';
        return $this;
    }

    protected function setLogs($logs) : void {
        $this->logs[] = $logs;
        $this->generateLogs();
    }

    protected function getLogs() : array {
        return $this->logs;
    }

    /**
     * Destructor to log Exceptions
     *
     */
    private function generateLogs() {
        $logs = $this->getLogs();
        if (!empty($logs)) {
            error_log(json_encode($logs));
        }
    }

    //abstract model methods
    /**
     * validateModel - Used to validate yaml Object
     *
     * @param object $yamlObject
     * @return boolean|Exception
     */
    abstract protected function validateModel(object $yamlObject) : bool| \Exception;
    /**
     * buildTableFromMeta - Builds table from JSON Object
     *
     * @param object $builderMeta
     * @return boolean|Exception
     */
    abstract protected function buildTableFromMeta(object $builderMeta) : bool | \Exception;
    /**
     * buildModelFromMeta - builds a POPO (Plain Old PHP Object) from YAML Object
     *
     * @param object $builderMeta
     * @return boolean|Exception
     */
    abstract protected function buildModelFromMeta(object $builderMeta) : bool | \Exception;
}