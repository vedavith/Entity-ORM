<?php
namespace EntityORM\EntityGenerator;

require_once '../vendor/autoload.php';
require_once '../EntityGenerator/GenerateModel.php';

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as Cmd;
use Symfony\Component\Console\Input\InputInterface as Input;
use Symfony\Component\Console\Input\InputOption as Inop;
use Symfony\Component\Console\Output\OutputInterface as Output;
use Symfony\Component\Console\Output\OutputOption as Outop;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\StreamOutput as Streamout;

//ModelGenerator
use EntityORM\EntityGenerator\GenerateModel as ModelGenerator;

//use EntityORM\EntityConnector\EntityDriver;

class BuildEntity extends Cmd {
    protected static $defaultName = 'create-model';
    private object $streamOut;
    private ?object $modGenObject;
    private object $yamlParser;

    public function __construct() {
        parent::__construct();
        //object for ModelGenerator
        $this->modGenObject = new ModelGenerator();
    }

    protected function configure() : void {
        $this->setName('create-model')
            ->setDescription('Creates a table and generates a model in models path')
            ->setHelp("Allows user to generate a table depending on model YAML file\n Usage: Entity:Gen generate-models --model=[modelName]")
            ->addOption('model', null, Inop::VALUE_REQUIRED)
            ->addOption('table', null, Inop::VALUE_REQUIRED);
    }

    protected function execute(Input $input, Output $output) : ?int
    {
        try {

            // Checking On json Model File Name
            $model = $input->getOption('model');
            if(empty($model)) {
                throw new \Exception("Please provide the file-name which has *.model.json extension");
            }

            // Should we create table or just update the model
            $table = $input->getOption('table') === 'true';

            // Reading models from given file path
            $path = '../JsonModels/'.$model.'.model.json';
            if(!file_exists($path)) {
                throw new \Exception("File not found");
            }

            // For compatability reasons we have changed to yaml to json
            $output->writeln('<comment>Reading a Json file...</comment>');
            $fileData = file_get_contents($path);
            $model = json_decode($fileData);
            $output->writeln('<comment>Generating Table and Models...</comment>');

            // Generating Models
            if($this->modGenObject->__builder(builderMeta: (object)$model, table: $table) instanceof \Exception) {
                throw new \Exception("Could not Create a Model");
            }

            return Cmd::SUCCESS;
        } catch (\Exception $ex) {
            $output->writeln("<error>".$ex->getMessage()."<error>");
            return Cmd::FAILURE;
        }
    }
}