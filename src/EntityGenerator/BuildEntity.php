<?php
namespace EntityForge\EntityGenerator;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as Cmd;
use Symfony\Component\Console\Input\InputInterface as Input;
use Symfony\Component\Console\Input\InputOption as Inop;
use Symfony\Component\Console\Output\OutputInterface as Output;
use Symfony\Component\Console\Output\OutputOption as Outop;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\StreamOutput as Streamout;

//ModelGenerator
use EntityForge\EntityGenerator\GenerateModel as ModelGenerator;

//use EntityForge\EntityConnector\EntityDriver;

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
            ->setDescription('Generates model and repository from a JSON model file')
            ->setHelp("Generates model and repository files from a JSON model definition\n Usage: Entity:Gen create-model --model=[modelName]")
            ->addOption('model', null, Inop::VALUE_REQUIRED);
    }

    protected function execute(Input $input, Output $output) : ?int
    {
        try {

            // Checking On json Model File Name
            $model = $input->getOption('model');
            if(empty($model)) {
                throw new \Exception("Please provide the file-name which has *.model.json extension");
            }

            // Table creation temporarily disabled; always update model only
            $table = false;

            // Reading models from given file path (use __DIR__ so path is correct regardless of CWD)
            // Json models are stored under src/JsonModels
            $path = __DIR__ . '/../JsonModels/' . $model . '.model.json';
            if(!file_exists($path)) {
                throw new \Exception("File not found");
            }

            // For compatability reasons we have changed to yaml to json
            $output->writeln('<comment>Reading a Json file...</comment>');
            $fileData = file_get_contents($path);
            $model = json_decode($fileData);
            $output->writeln('<comment>Generating Models and Repositories...</comment>');

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