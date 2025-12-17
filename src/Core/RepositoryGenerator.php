<?php
namespace EntityForge\Core;

class RepositoryGenerator
{
    private string $className;
    private string $path;

    public function __construct()
    {
        $this->path = __DIR__ . '/../../src/EntityRepository/';
    }

    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function generateRepository(\stdClass $model) : ?bool
    {
        $template = $this->fileGenerator($model);
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
        if (is_writable($this->path)) {
            return (bool)file_put_contents($this->path . $this->className . ".php", $template);
        }
        return false;
    }

    private function fileGenerator(\stdClass $modelData) : string
    {
        $this->className = $modelData->model . 'Repository';

        // determine table name: explicit table property or guess from model name
        $table = property_exists($modelData, 'table') && !empty($modelData->table)
            ? $modelData->table
            : $this->guessTableName($modelData->model);

        // determine primary key from fields if marked
        $primary = 'id';
        if (property_exists($modelData, 'fields') && is_object($modelData->fields)) {
            foreach ($modelData->fields as $fname => $fmeta) {
                if (is_object($fmeta) && property_exists($fmeta, 'primary')) {
                    if ($fmeta->primary === true || $fmeta->primary === 'true') {
                        $primary = $fname;
                        break;
                    }
                }
            }
        }

        $tpl = "<?php\n";
        $tpl .= "namespace EntityForge\\EntityRepository;\n\n";
        $tpl .= "use EntityForge\\EntityConnector\\EntityDriver;\n\n";
        $tpl .= "class {$this->className} extends BaseRepository\n";
        $tpl .= "{\n";
        $tpl .= "    protected string \$table = '{$table}';\n";
        $tpl .= "    protected string \$primaryKey = '{$primary}';\n\n";
        $tpl .= "    public function __construct(EntityDriver \$driver)\n";
        $tpl .= "    {\n";
        $tpl .= "        parent::__construct(\$driver, \$this->table, \$this->primaryKey);\n";
        $tpl .= "    }\n";
        $tpl .= "}\n";

        return $tpl;
    }

    private function guessTableName(string $modelName): string
    {
        // convert CamelCase to snake_case
        $snake = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $modelName));
        // simple pluralize: append 's' if not already ending with 's'
        if (substr($snake, -1) !== 's') {
            $snake .= 's';
        }
        return $snake;
    }
}
