<?php
namespace EntityORM\EntityModels\ModelMeta;
abstract class IModelMeta {
    private string $int;
    private string $float;
    private string $dateTime;
    private string $maxLength;
    private bool $autoIncrement;

    //todo: Add getters and setters for keywords
   
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

    //abstract model methods
    /**
     * validateModel - Used to validate yaml Object
     *
     * @param object $yamlObject
     * @return boolean|Exception
     */
    abstract protected function validateModel(object $yamlObject) : bool| \Exception;
    /**
     * buildTableFromMeta - Builds table from YAML Object
     *
     * @param object $yamlObject
     * @return boolean|Exception
     */
    abstract protected function buildTableFromMeta(object $yamlObject) : bool | \Exception;
    /**
     * buildModelFromMeta - builds a POPO (Plain Old PHP Object) from YAML Object
     *
     * @param object $yamlObject
     * @return boolean|Exception
     */
    abstract protected function buildModelFromMeta(object $yamlObject) : bool | \Exception;   
}