<?php

namespace WebEtDesign\MediaBundle\Command;

use Sonata\Doctrine\Entity\BaseEntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use WebEtDesign\MediaBundle\Entity\Media;

class RemoveUselessMediaCommand extends Command
{

    protected static $defaultName = 'media:removeUseless';
    protected static $defaultDescription = 'Analyse all media in public/upload and remove useless media.';
    private $entitymanager;

    /**
     * ImplantationRegenerateSlugCommand constructor.
     * @param string|null $name
     */
    public function __construct(string $name = null, BaseEntityManager $entityManager)
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

        $em = $this->entitymanager->getEntityManager();
        $mediaBD = $em->getRepository(Media::class)->findAll();

        foreach ($finder as $file) {
            if (is_null($em->getRepository(Media::class)->findBy(['file_name'=>$file->getFilename()])) ||
            empty($em->getRepository(Media::class)->findBy(['file_name'=>$file->getFilename()]))) {
                unlink($file);
            }
        }

    }

}