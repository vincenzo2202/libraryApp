<?php

namespace App\Maker;

use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class MakeService extends AbstractMaker
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getCommandName(): string
    {
        return 'make:serviceManager';
    }

    public static function getCommandDescription(): string
    {
        return 'Crea el servicio manager de la entidad';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {

        $command
            ->addArgument('name', InputArgument::OPTIONAL, sprintf('Choose a name for your Entity (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())));
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $entityClassName = $input->getArgument('name');

        if (!$this->entityExists($entityClassName)) {
            throw new RuntimeException(sprintf('La entidad "%s" no existe.', $entityClassName));
        }

        $classNameDetails = $generator->createClassNameDetails(
            $input->getArgument('name') . "Manager",
            'Service\\',
            'Service'
        );

        $nameVariable = lcfirst($input->getArgument('name'));
        $entity_class_name = $input->getArgument('name');

        $generator->generateClass(
            $classNameDetails->getFullName(),
            __DIR__ . '/../Resources/skeleton/service/Service.tpl.php',
            [
                'nameVariable' => $nameVariable,
                'entity_class_name' => $entity_class_name,
            ]
        );

        $generator->writeChanges();

        $io->success('Service class created successfully.');
    }

    private function entityExists(string $entityClassName): bool
    {
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($metadata as $classMetadata) {
            if ($classMetadata->getName() === 'App\\Entity\\' . $entityClassName) {
                return true;
            }
        }
        return false;
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        // Add any dependencies required by your maker
    }
}
