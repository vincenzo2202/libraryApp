<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use App\Entity\<?= $entity_class_name ?>;
use App\Repository\<?= $entity_class_name ?>Repository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class <?= $class_name ?>

{
public function __construct(
private <?= $entity_class_name ?>Repository $<?= $nameVariable ?>RE,
private EntityManagerInterface $em
) {}

public function selector()
{
return $this-><?= $nameVariable ?>RE->getSelector();
}

public function edit(int $id, $request): <?= $entity_class_name ?>

{
$<?= $nameVariable ?> = $this-><?= $nameVariable ?>RE->findOrFail($id);
$<?= $nameVariable ?> = $this-><?= $nameVariable ?>RE->writeFromRequest($request, $<?= $nameVariable ?>);
return $<?= $nameVariable ?>;
}

public function create(Request $request): <?= $entity_class_name ?>

{
$request = json_decode($request->getContent(), true);
$<?= $nameVariable ?> = $this-><?= $nameVariable ?>RE->writeFromRequest($request);
return $<?= $nameVariable ?>;
}

public function delete(int $id): void
{
$<?= $nameVariable ?> = $this-><?= $nameVariable ?>RE->findOrFail($id);
$this-><?= $nameVariable ?>RE->remove($<?= $nameVariable ?>);
}
}