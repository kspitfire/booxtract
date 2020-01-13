<?php

namespace Booxtract\Console;

use Booxtract\Services\BookDataService;
use Booxtract\Services\Parsers\FictionBookParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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
     * @var Filesystem
     */
    private $fs;

    /**
     * @var BookDataService
     */
    private $service;

    public function __construct(BookDataService $service)
    {
        $this->finder = new Finder();
        $this->fs = new Filesystem();
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
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'This will simulate cleaning and show you what would happen')
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

        $startedAt = new \DateTime();
        $output->writeln(sprintf('<info>Started at %s</info>', $startedAt->format('Y-m-d H:i:s')));

        // FB2
        try {
            $this->processFb2($input, $output);
        } catch (\Exception $ex) {
            $output->writeln(sprintf('<error>%s</error>', $ex->getMessage()));
        }

        return 0;
    }

    private function processFb2(InputInterface $input, OutputInterface $output)
    {
        if ($output->isVerbose()) {
            $output->writeln(sprintf('Starting FB2 processing ... '));
        }

        $files = $this->finder->name(FictionBookParser::FILE_MASK)->files()->in($input->getOption('path'));
        $this->service->setParser(new FictionBookParser());

        if ($output->isVerbose()) {
            $output->writeln(sprintf('Found FB2 file(s): <info>%d</info>', $files->count()));
            $output->writeln('');
        }

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            if ($output->isVerbose()) {
                $output->writeln('-------------');
                $output->writeln(sprintf('Parsing metadata from file "%s"', $file->getFilename()));
            }

            $data = $this->service->parse($file);
            $newFilename = $this->service->getProperFileName($data, 'fb2');

            if ($output->isVerbose()) {
                $output->writeln(sprintf('Suggested filename (%d): <comment>%s</comment>', strlen($newFilename), $newFilename));
            }
        }

        $output->writeln('');
        $finishedAt = new \DateTime();
        $output->writeln(sprintf('<info>Finished at %s</info>', $finishedAt->format('Y-m-d H:i:s')));
    }
}