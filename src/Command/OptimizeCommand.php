<?php

namespace WebEtDesign\MediaBundle\Command;

use Spatie\ImageOptimizer\OptimizerChain;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Spatie\ImageOptimizer\Optimizers\Pngquant;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

class OptimizeCommand extends Command
{
    protected static $defaultName = 'media:optimize';
    protected static $defaultDescription = 'Analyse all media in public/upload and optimise them if possible.';

    /**
     * ImplantationRegenerateSlugCommand constructor.
     * @param string|null $name
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $finder = new Finder();
        $finder->files()->in('public/upload');
        $io->progressStart($finder->count());
        foreach ($finder as $file) {
            $mimeType = MimeTypeGuesser::getInstance()->guess($file->getPathname());

            if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/tiff'])) {
                $optimizerChain = OptimizerChainFactory::create(['quality' => 75]);
                $optimizerChain->optimize($file->getPathname());
            }
            $io->progressAdvance();
        }
        $io->progressFinish();

        return 0;
    }
}
