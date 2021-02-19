<?php

namespace Booxtract\Console;

use Booxtract\Services\BookDataService;
use Booxtract\Services\Parsers\BookParserInterface;
use Booxtract\Services\Parsers\FictionBookParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\IOException;
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
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Path to analyze ebook files')
            ->addOption('manual', 'm', InputOption::VALUE_NONE, 'Asks every file for renaming, shows suggested file name for every file')
            ->addOption('dry-run', 'd', InputOption::VALUE_NONE, 'All collected book data will be shown, files will not be renamed')
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
            $output->writeln(sprintf('<error>%s</error>%sTrace: %s', $ex->getMessage(), "\n", $ex->getTraceAsString()));
        }

        return 0;
    }

    private function processFb2(InputInterface $input, OutputInterface $output)
    {
        if ($output->isVerbose()) {
            $output->writeln(sprintf('Starting FB2 processing ... '));
        }

        $parser = new FictionBookParser();
        $this->process($parser, $input, $output);
    }

    private function process(BookParserInterface $parser, InputInterface $input, OutputInterface $output)
    {
        $this->service->setParser($parser);
        $path = $input->getOption('path');

        // unzip zip-archives
        $zipMask = sprintf('%s.zip', $parser::getFileMask());
        $this->finder = new Finder();
        $zips = $this->finder->name($zipMask)->files()->in($path);

        if ($zips->count() > 0) {
            if ($output->isVerbose()) {
                $output->writeln(sprintf('Found zip(s): <info>%d</info>', $zips->count()));
                $output->writeln('');
            }

            /** @var SplFileInfo $zip */
            foreach ($zips as $zip) {
                try {
                    $archive = new \ZipArchive();
                    $resource = $archive->open($zip->getRealPath());

                    if (true === $resource) {
                        $archive->extractTo($zip->getPath());
                        $archive->close();

                        if ($output->isVerbose()) {
                            $output->writeln(sprintf('Extracted %s to %s', $zip->getFilename(), $zip->getPath()));
                        }

                        unlink($zip->getRealPath());
                    } else {
                        if ($output->isVerbose()) {
                            $output->writeln(sprintf('<error>Cannot open zip archive: %s, skipping</error>', $zip->getFilename()));
                        }
                    }
                } catch (\Exception $ex) {
                    $output->writeln(sprintf(
                        '<error>Error while processing archives: %s,%sTrace: %s</error>',
                        $ex->getMessage(),
                        "\n",
                        $ex->getTraceAsString()
                    ));
                }
            }
        }

        $this->finder = new Finder();
        $files = $this->finder->name($parser::getFileMask())->files()->in($path);

        if ($output->isVerbose()) {
            $output->writeln(sprintf('Found file(s): <info>%d</info>', $files->count()));
            $output->writeln('');
        }

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            if ($output->isVerbose()) {
                $output->writeln('-------------');
                $output->writeln(sprintf('Parsing metadata from file "%s"', $file->getFilename()));
            }

            try {
                $data = $this->service->parse($file);
                $newFilename = $this->service->getProperFileName($data, 'fb2');
            } catch (\Exception $ex) {
                $output->writeln(sprintf('<error>Parsing exception (this file will be skipped) </error>: %s', $ex->getMessage()));
                continue;
            }

            if ($output->isVerbose()) {
                $output->writeln(sprintf('Suggested filename: <comment>%s</comment>', $newFilename));
            }

            if (false === $input->getOption('dry-run')) {
                if ($file->getFilename() !== $newFilename) {
                    $approvedRenaming = true;

                    if (true === $input->getOption('manual')) {
                        $io = new SymfonyStyle($input, $output);
                        $confirmMsg = (false === $output->isVerbose())
                            ? sprintf('Rename "<comment>%s</comment>" to "<comment>%s</comment>"?', $file->getFilename(), $newFilename)
                            : 'Rename?';
                        $approvedRenaming = $io->confirm($confirmMsg, true);
                    }

                    if (true === $approvedRenaming) {
                        if ($output->isVerbose()) {
                            $output->write(sprintf('Renaming %s to %s ...', $file->getFilename(), $newFilename));
                        }

                        try {
                            $this->fs->rename($file->getRealPath(), sprintf('%s/%s', $file->getPath(), $newFilename));

                            if ($output->isVerbose()) {
                                $output->writeln(' OK');
                            }
                        } catch (IOException $IOException) {
                            $output->writeln('');
                            $output->writeln(sprintf('<error>Could not rename %s, skipping</error>', $file->getFilename()));
                        }
                    }
                } else {
                    if ($output->isVerbose()) {
                        $output->writeln('File has the proper name');
                    }
                }
            }
        }

        $output->writeln('');
        $finishedAt = new \DateTime();
        $output->writeln(sprintf('<info>Finished at %s</info>', $finishedAt->format('Y-m-d H:i:s')));
    }
}
