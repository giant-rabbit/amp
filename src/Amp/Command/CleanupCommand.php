<?php
namespace Amp\Command;

use Amp\Instance;
use Amp\InstanceRepository;
use Amp\Util\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupCommand extends ContainerAwareCommand {

  /**
   * @var InstanceRepository
   */
  private $instances;

  /**
   * @var Filesystem
   */
  private $fs;

  /**
   * @param \Amp\Application $app
   * @param string|null $name
   * @param array $parameters list of configuration parameters to accept ($key => $label)
   */
  public function __construct(\Amp\Application $app, $name = NULL, InstanceRepository $instances) {
    $this->instances = $instances;
    $this->fs = new Filesystem();
    parent::__construct($app, $name);
  }

  protected function configure() {
    $this
      ->setName('cleanup')
      ->setDescription('Destroy any stale MySQL/Apache instances (linked to old/non-existent paths)')
      ->addOption('force', 'f', InputOption::VALUE_NONE, 'Destroy ALL databases (regardless of whether the code exists)');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $count = 0;
    foreach ($this->instances->findAll() as $instance) {
      if ($input->getOption('force') || !file_exists($instance->getRoot())) {
        $output->writeln("Destroy (root={$instance->getRoot()}, name={$instance->getName()}, dsn={$instance->getDsn()})");
        $this->instances->remove($instance->getId());
        $count++;
      } else {
        $output->writeln("Skip (root={$instance->getRoot()}, name={$instance->getName()}, dsn={$instance->getDsn()})");
      }
    }

    $output->writeln("Destroyed {$count} instance(s)");

    $this->instances->save();
  }
}