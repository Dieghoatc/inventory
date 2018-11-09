<?php
require __DIR__.'/../vendor/autoload.php';

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\ArrayInput;

$kernel = new Kernel('test', true); // create a "test" kernel
$application = new Application($kernel);
$application->setAutoExit(false);
$output = new ConsoleOutput();

$inputs = [
    new ArrayInput([
        'command' => 'doctrine:database:drop',
        '--force' => true,
    ]),
    new ArrayInput([
        'command' => 'doctrine:database:create'
    ]),
    new ArrayInput([
        'command' => 'doctrine:schema:create'
    ]),
    new ArrayInput([
        'command' => 'doctrine:fixtures:load'
    ])
];

foreach ($inputs as $input){
    $application->run($input, new ConsoleOutput());
}

$output->writeln('Data was successfully prepared [OK]');