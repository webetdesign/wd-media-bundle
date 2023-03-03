<?php
declare(strict_types=1);

namespace WebEtDesign\MediaBundle\Command;

use Spatie\ImageOptimizer\OptimizerChainFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Mime\MimeTypes;

class OptimizeCommand extends Command
{
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
            ->setName('media:optimize')
            ->setDescription('Analyse all media in public/upload and optimise them if possible.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $finder = new Finder();
        $finder->files()->in('public/upload');
        $io->progressStart($finder->count());
        foreach ($finder as $file) {
            $mimeTypes = new MimeTypes();
            $mimeType = $mimeTypes->guessMimeType($file->getPathname());

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
