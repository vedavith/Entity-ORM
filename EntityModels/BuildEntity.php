<?php
namespace EntityORM\EntityModels;

require_once '../vendor/autoload.php';
require_once '../EntityModels/GenerateModel.php';

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command as Cmd;
use Symfony\Component\Console\Input\InputInterface as Input;
use Symfony\Component\Console\Input\InputOption as Inop;
use Symfony\Component\Console\Output\OutputInterface as Output;
use Symfony\Component\Console\Output\OutputOption as Outop;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\StreamOutput as Streamout;

//ModelGenerator
use EntityORM\EntityModels\GenerateModel as ModelGenerator;




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
            ->setHelp('Allows user to generate a table depending on model YAML file')
            ->addOption('model', null, Inop::VALUE_OPTIONAL);
    }

    protected function execute(Input $input, Output $output) : ?int {
        $model = $input->getOption('model');
        if($model) {
            $path = '../yamlModels/'.$model.'.model.yaml';
            if(!file_exists($path)) {
                $output->writeln("<error>File not found</error>");
                return Cmd::FAILURE;
            } 
            
            try{
                $yamlToArray = yaml_parse_file($path, 0);
                var_dump($yamlToArray);
                $output->writeln('<comment>Reading a YAML file...</comment>');
                $output->writeln('<comment>Generating Model...</comment>');
                
                //todo: creating a table from the yaml data
                if($this->modGenObject->__builder((object)$yamlToArray) instanceof \Exception){
                    throw new \Exception("Could not Create a Model"); 
                }

                return Cmd::SUCCESS;
            } catch (\Exception $ex) {
                $output->writeln("<error>".$ex->getMessage()."<error>");
            } finally {
               //do final stuff - returning 
               return Cmd::SUCCESS;
            }
        } else {
            $output->writeln("Please provide the file-name which has *.model.yaml extension");
            return Cmd::FAILURE;
        }
    }

    private function yamlParser($filepath, $seek = 0) {
        return yaml_parse_file();
    }

}