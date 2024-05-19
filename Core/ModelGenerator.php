<?php
namespace EntityORM\Core;

class ModelGenerator
{
    private string $className;
    private string $path;

    public function __construct()
    {
        $this->path = "../EntityModels/";
    }

    public function generateModel(\stdClass $model) : ?bool
    {
        $template = $this->fileGenerator($model);
        $fileData = false;
        if (is_writable(dirname($this->path))) {
            $fileData = file_put_contents($this->path.$this->className.".php", $template);
        }
        return $fileData;
    }

    private function fileGenerator($modelData) : ?string
    {
        $this->className = $modelData->model;
        // PHP Template
        $fileTemplate = "<?php\n\n";
        $fileTemplate.= "// This File is Generated with Entity ORM. \n\n";
        $fileTemplate.= "namespace EntityORM\EntityModels;\n\n";
        $fileTemplate.= "class ".$this->className."\n";
        $fileTemplate .= "{\n\n";

        $fields = $modelData->fields;
        foreach ($fields as $field => $fieldSym) {

            // Checking for types
            $type = $fieldSym->type;
            if ($type == 'datetime' || $type == 'date') {
                $type = '\DateTime';
            }

            $fileTemplate.= "\t/** @var ".$type." */\n";
            $fileTemplate.= "\t public ".$type." $".$field.";\n\n";
        }

        $prop = "property";
        $value = "value";
        $currentInstance = "this";
        $fileTemplate .= "\t/** __get **/\n";
        $fileTemplate .= "\tpublic function __get($".$prop.") {\n";
        $fileTemplate .= "\t if (property_exists(\$".$currentInstance.",\$".$prop.")) {\n";
        $fileTemplate .= "\t\t return \$".$currentInstance."->\$".$prop."();\n";
        $fileTemplate .= "\t }\n";
        $fileTemplate .= "\t}\n\n";
        $fileTemplate .= "\t/** __set **/\n";
        $fileTemplate .= "\tpublic function __set(\$".$prop.",\$".$value.") {\n";
        $fileTemplate .= "\t if (property_exists($".$currentInstance.", $".$prop.")) {\n";
        $fileTemplate .= "\t\t \$".$currentInstance."->\$".$prop." = "."\$$value;\n";
        $fileTemplate .= "\t  }\n";
        $fileTemplate .= "\t\t return \$".$currentInstance.";\n";
        $fileTemplate .= "\t }\n\n";
        $fileTemplate.= "}";

        return $fileTemplate;
    }
}