<?php

namespace WebEtDesign\MediaBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use WebEtDesign\MediaBundle\Entity\Media;

class MediaImportSonataMediaCommand extends Command
{
    protected static               $defaultName        = 'media:import-sonata-media';
    protected static string        $defaultDescription = 'Add a short description for your command';
    protected ?string              $mediaClass;
    protected ?array               $wdMediaConfig;
    protected array                $contextToCategory  = [];
    protected SymfonyStyle         $io;
    protected array                $wdMediaCategories;
    protected string               $mediaFolder;
    protected OutputInterface      $output;
    private EntityManagerInterface $em;
    private ParameterBagInterface  $parameterBag;
    private Pool                   $poolProvider;

    public function __construct(
        EntityManagerInterface $em,
        ParameterBagInterface $parameterBag,
        Pool $poolProvider,
        KernelInterface $kernel,
        string $name = null
    ) {
        parent::__construct($name);
        $this->em           = $em;
        $this->parameterBag = $parameterBag;
        $this->poolProvider = $poolProvider;
        $this->kernel       = $kernel;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output            = $output;
        $this->mediaClass        = $this->parameterBag->get('sonata.media.media.class');
        $this->wdMediaConfig     = $this->parameterBag->get('wd_media.categories');
        $this->wdMediaCategories = array_keys($this->wdMediaConfig);

        $this->mediaFolder = $this->parameterBag->get('kernel.project_dir') . '/public/upload/media/';

        $this->io = new SymfonyStyle($input, $output);


        $this->io->title('Migration from sonata to wd-media');

        $isValidConfiguration = $this->setupCategories();

        if (!$isValidConfiguration) {
            $this->io->error('Abort');
            exit(2);
        }

        $this->io->section('Media importation');

        $sonataMedias = $this->em->getRepository($this->mediaClass)->findAll();

        $count = 0;

        $this->io->progressStart(count($sonataMedias));
        foreach ($sonataMedias as $sonataMedia) {
            $ref = $this->getMediaReference($sonataMedia);
            if (file_exists($this->mediaFolder . $ref)) {
                $media = $this->createMedia($sonataMedia);
                $this->em->persist($media);
                $count++;
            } else {
                $this->io->newLine();
                $this->io->text('<options=bold>[Skip]</> Le référence du media <options=bold,underscore>' . $sonataMedia->getId() . '</> n\'existe pas');
            }
            $this->io->progressAdvance();
        }

        $this->em->flush();

        $this->io->progressFinish();

        $this->io->success('Migration terminée, ' . $count . ' medias importés');

        return 0;
    }

    public function createMedia(\App\Application\Sonata\MediaBundle\Entity\Media $sonataMedia
    ): Media {

        $ref = $this->getMediaReference($sonataMedia);

        $file = new UploadedFile($this->mediaFolder . $ref, $sonataMedia->getName(),
            $sonataMedia->getContentType(), null, true);

        $media = new Media();
        $media
            ->setCategory($this->contextToCategory[$sonataMedia->getContext()] ?? $sonataMedia->getContext())
            ->setFile($file);

        return $media;
    }

    public function setupCategories(): bool
    {
        $this->io->section('Configuration checkup');

        $diff = array_diff($this->getSanataContexts(), $this->wdMediaCategories);

        if (count($diff) > 0) {
            $this->io->text([
                'The following categories are not defined.',
                '<options=bold>' . implode(', ', $diff) . '</>',
                'You can use this maker for add it: <fg=yellow>make:media-category</>'
            ]);

            return false;
        }

        return true;
    }

    private function getSanataContexts(): array
    {
        /** @var QueryBuilder $qb */
        $qb = $this->em->getRepository($this->mediaClass)->createQueryBuilder('m');

        $qb->select('distinct m.context');

        $r = $qb->getQuery()->getScalarResult();

        return array_column($r, 'context');
    }


    /**
     * @param \App\Application\Sonata\MediaBundle\Entity\Media $sonataMedia
     */
    private function getMediaReference($sonataMedia): string
    {
        $provider = $this->poolProvider->getProvider($sonataMedia->getProviderName());
        $ref      = $provider->getReferenceImage($sonataMedia);

        return $ref;
    }
}
