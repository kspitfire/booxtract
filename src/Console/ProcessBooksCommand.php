<?php

namespace Booxtract\Console;

use Booxtract\Services\BookDataService;
use Booxtract\Services\Parsers\FictionBookParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ProcessBooksCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'books:process';

    /**
     * @var Finder
     */
    private $finder;

    /**
     * @var BookDataService
     */
    private $service;

    public function __construct(BookDataService $service)
    {
        $this->finder = new Finder();
        $this->service = $service;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Analyze files')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Path to analyze')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'This will simulate cleaning and show you what would happen')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (true === empty($input->getOption('path'))) {
            $output->writeln('<error>Path has not been specified</error>');
            return 1;
        }

        // FB2
        $files = $this->finder->name(FictionBookParser::FILE_MASK)->files()->in($input->getOption('path'));
        $this->service->setParser(new FictionBookParser());

        foreach ($files as $file) {
            $data = $this->service->parse($file);
            dump($this->service->getProperFileName($data, 'fb2'));
//            dump($data);
        }

        return 0;
    }
}