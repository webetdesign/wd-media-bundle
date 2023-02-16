<?php

namespace WebEtDesign\MediaBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use WebEtDesign\MediaBundle\Entity\Media;

class RemoveUselessMediaCommand extends Command
{

    protected static $defaultName = 'media:removeUseless';
    protected static $defaultDescription = 'Analyse all media in public/upload and remove useless media.';
    private EntityManagerInterface $entitymanager;

    /**
     * ImplantationRegenerateSlugCommand constructor.
     * @param string|null $name
     */
    public function __construct(string $name = null, EntityManagerInterface $entityManager)
    {
        parent::__construct($name);
        $this->entitymanager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $finder = new Finder();
        $finder->files()->in('public/upload');

        $repo = $this->entitymanager->getRepository(Media::class);


        foreach ($finder as $file) {
            if (is_null($repo->findBy(['file_name'=>$file->getFilename()])) ||
            empty($repo->findBy(['file_name'=>$file->getFilename()]))) {
                unlink($file);
            }
        }

    }

}