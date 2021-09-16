<?php

namespace WebEtDesign\MediaBundle\Maker;

use RuntimeException;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Yaml\Yaml;

class MakeCategory extends AbstractMaker
{

    protected ConsoleStyle        $io;
    protected array               $devicesCode;
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public static function getCommandName(): string
    {
        return 'make:media-category';
    }

    public function getCommandDescription(): string
    {
        return 'Add new media category in configuration file';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->addArgument('code', InputArgument::OPTIONAL,
                'The code of the category (e.g. <fg=yellow>default</>)')
            ->addArgument('label', InputArgument::OPTIONAL,
                'The label of the category (e.g. <fg=yellow>Default category</>)')
            ->addOption('forceCode', null, InputOption::VALUE_NONE);
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $this->io          = $io;
        $this->devicesCode = array_keys($this->parameterBag->get('wd_media.responsive'));

        $code  = $input->getArgument('code');
        $label = $input->getArgument('label');

        $configFile = $this->parameterBag->get('kernel.project_dir') . '/config/packages/webetdesign/wd_media.yaml';

        $globalConfig = Yaml::parse(file_get_contents($configFile));

        $configs = &$globalConfig['wd_media']['categories'];
        $retry   = false;
        do {
            if ($retry) {
                if (!$input->getOption('forceCode')) {
                    $code = $this->io->ask(
                        'The code of the category (e.g. <fg=yellow>default</>)',
                        $code
                    );
                }
                $label = $this->io->ask('The label of the category (e.g. <fg=yellow>Default category</>)',
                    $label);
            }
            $retry = true;

            $config = $configs[$code] ?? [
                    'label'   => $label,
                    'formats' => [],
                ];

            $this->addFormat($config['formats'], count($config['formats']) === 0);

            $this->io->text(Yaml::dump([$code => $config], 10, 2));
            $confirm = $this->io->confirm('Do you want to save this configuration ?');
        } while (!$confirm);

        $configs[$code] = $config;
        file_put_contents($configFile, Yaml::dump($globalConfig, 10, 2));
    }

    private function addFormat(array &$config, bool $required)
    {
        if ($required) {
            $name = $this->io->ask('Required format (e.g. <fg=yellow>thumb</>)', null,
                function ($v) {
                    if (empty($v)) {
                        throw new RuntimeException('This value cannot be blank.');
                    }
                    return $v;
                });
        } else {
            $name = $this->io->ask('Add an other format (e.g. <fg=yellow>thumb</>) <fg=white>[Leave blank to continue]</>');
        }

        if (!empty($name)) {
            $formats = $config[$name] ?? [];

            $this->addDevice($formats, count($formats) === 0);

            $config[$name] = $formats;

            $this->addFormat($config, false);
        }
    }

    private function addDevice(array &$config, bool $required)
    {
        if ($required) {
            $device = $this->io->choice('Required device', $this->devicesCode);
        } else {
            $device = $this->io->choice('Add an other device <fg=white>[Leave blank to continue]</>',
                array_merge(array_diff($this->devicesCode, array_keys($config)), [null]));
        }

        if (!empty($device)) {
            $config[$device] = ['filters' => []];
            $this->addDevice($config, false);
        }
    }
}
