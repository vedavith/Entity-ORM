<?php
namespace EntityForge\Tests;

use PHPUnit\Framework\TestCase;
use EntityForge\EntityGenerator\GenerateModel;

class GenerateModelTest extends TestCase
{
    public function testGenerateModelAndRepositoryCreatesFiles()
    {
        $jsonPath = __DIR__ . '/../src/JsonModels/users.model.json';
        $this->assertFileExists($jsonPath, 'Source JSON model not found for test');

        $json = file_get_contents($jsonPath);
        $model = json_decode($json);

        $gen = new GenerateModel();
        $ok = $gen->__builder((object)$model, false);

        $this->assertTrue($ok, 'Generator reported failure');

        $modelPath = __DIR__ . '/../src/EntityModels/' . $model->model . '.php';
        $repoPath = __DIR__ . '/../src/EntityRepository/' . $model->model . 'Repository.php';

        $this->assertFileExists($modelPath, 'Model file was not created');
        $this->assertFileExists($repoPath, 'Repository file was not created');

        $this->assertStringContainsString('class ' . $model->model, file_get_contents($modelPath));
        $this->assertStringContainsString('class ' . $model->model . 'Repository', file_get_contents($repoPath));
    }
}
