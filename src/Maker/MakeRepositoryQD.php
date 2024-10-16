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
use Symfony\Component\Filesystem\Filesystem;

class MakeRepositoryQD extends AbstractMaker
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public static function getCommandName(): string
    {
        return 'make:repository';
    }

    public static function getCommandDescription(): string
    {
        return 'Adaptar el repositorio de la entidad a la estructura de Quasar Dynamics';
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


        $repositoryClassName = 'App\\Repository\\' . $entityClassName . 'Repository';
        $repositoryFilePath = $this->getRepositoryFilePath($repositoryClassName);

        if (!file_exists($repositoryFilePath)) {
            throw new RuntimeException(sprintf('El repositorio para la entidad "%s" no existe.', $entityClassName));
        }

        $nameVariable = lcfirst($input->getArgument('name'));
        $entityClassName = $input->getArgument('name');

        $this->addFunctionsToRepository($repositoryFilePath, $nameVariable, $entityClassName);


        $io->success('Repository was update successfully.');
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

    private function getRepositoryFilePath(string $repositoryClassName): string
    {
        $reflector = new \ReflectionClass($repositoryClassName);
        return $reflector->getFileName();
    }

    private function addFunctionsToRepository(string $repositoryFilePath, $nameVariable, $entityClassName): void
    {
        $filesystem = new Filesystem();
        $repositoryContent = file_get_contents($repositoryFilePath);
        $setters = $this->generateSetters($entityClassName, $nameVariable);

        $newFunction = <<<PHP

    public function findOrFail(int \$id): $entityClassName
    {
        \$$nameVariable = \$this->find(\$id);
        if (!\$$nameVariable) throw new NotFoundException("$entityClassName no encontrado");

        return \$$nameVariable;
    }

    public function getSelector(): array
    {
        // TODO: Implement getSelector() method.
    }

    public function setPropertiesIfFound(Request \$request, $entityClassName \$$nameVariable): $entityClassName
    {
$setters
        return \$$nameVariable;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove($entityClassName \$entity, bool \$flush = true): void
    {
        \$this->_em->remove(\$entity);
        if (\$flush) {
            \$this->_em->flush();
        }
    }

    public function writeFromRequest(\$request, \$entityToEdit = null): $entityClassName
    {
        \$request = RepositoryUtilities::arrayToRequest(\$request);

        if (\$entityToEdit instanceof $entityClassName) {
            \$$nameVariable = \$entityToEdit;
            \$inCreationTime = false;
        } else {
            \$$nameVariable = new $entityClassName();
            \$inCreationTime = true;
        }

        \$$nameVariable = \$this->setPropertiesIfFound(\$request, \$$nameVariable, \$inCreationTime);

        \$this->_em->persist(\$$nameVariable);

        return \$$nameVariable;
    }

PHP;

        // Insert the new function before the last closing brace
        $repositoryContent = preg_replace('/}\s*$/', $newFunction . '}', $repositoryContent);

        $filesystem->dumpFile($repositoryFilePath, $repositoryContent);
    }

    private function generateSetters(string $entityClassName, string $nameVariable): string
    {
        $reflector = new \ReflectionClass('App\\Entity\\' . $entityClassName);
        $properties = $reflector->getProperties();
        //dd($properties);
        $setters = '';

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            if ($propertyName === 'id') {
                continue;
            }
            $setterName = 'set' . ucfirst($propertyName);
            $propertyType = $property->getType();
            if ($propertyType && !$propertyType->isBuiltin()) {
                $relatedClassName = $propertyType->getName();
                $relatedClassShortName = (new \ReflectionClass($relatedClassName))->getShortName();
                //poner el primer caracter en mayuscula
                $propertyName = ucfirst($propertyName);
                // poner el primer caracter en minuscula
                $propertyNameLower = lcfirst($propertyName);
                $repositoryClassName = $propertyName . 'Repository';
                $variableRepository = lcfirst($propertyName) . 'Repository';
                $variableRelation = lcfirst($propertyName);

                $setters .= <<<PHP
        if (\$request->get('$propertyNameLower') !== null) {
            \$$variableRepository = new $repositoryClassName(\$this->registry);
            \$$variableRelation = \$$variableRepository
PHP;
                $setters .= <<<PHP
->findOrFail(\$request->get('$propertyNameLower'));
            \$$nameVariable->$setterName(\$$variableRelation);
        }

PHP;
            } else {
                $propertyNameLower = lcfirst($propertyName);
                $setters .= <<<PHP
        \$request->get('$propertyNameLower') === null ? '' : \$$nameVariable->$setterName(\$request->get('$propertyNameLower'));

PHP;
            }
        }

        return $setters;
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
        // Add any dependencies required by your maker
    }
}
