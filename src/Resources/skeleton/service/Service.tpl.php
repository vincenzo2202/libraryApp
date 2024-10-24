<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use App\Entity\<?= $entity_class_name ?>;
use App\Repository\<?= $entity_class_name ?>Repository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;

class <?= $class_name ?>

{
private $security;

public function __construct(
private <?= $entity_class_name ?>Repository $<?= $nameVariable ?>RE,
private EntityManagerInterface $em,
Security $security
) {
$this->security = $security;
}

public function selector()
{
return $this-><?= $nameVariable ?>RE->getSelector();
}

public function edit(int $id, $request): <?= $entity_class_name ?>

{
$<?= $nameVariable ?> = $this-><?= $nameVariable ?>RE->findOrFail($id);
$<?= $nameVariable ?> = $this-><?= $nameVariable ?>RE->writeFromRequest($request, $<?= $nameVariable ?>);

try {
$this->em->flush();
} catch (\Exception $e) {
throw new \Exception($e->getMessage());
}

return $<?= $nameVariable ?>;
}

public function create(Request $request): <?= $entity_class_name ?>

{
$request = json_decode($request->getContent(), true);
$<?= $nameVariable ?> = $this-><?= $nameVariable ?>RE->writeFromRequest($request);

try {
$this->em->flush();
} catch (\Exception $e) {
throw new \Exception($e->getMessage());
}

return $<?= $nameVariable ?>;
}

public function delete(int $id): void
{
$<?= $nameVariable ?> = $this-><?= $nameVariable ?>RE->findOrFail($id);
$this-><?= $nameVariable ?>RE->remove($<?= $nameVariable ?>);
}

public function tokenUserId(): int
{
return $this->security->getUser()->getId();
}
}