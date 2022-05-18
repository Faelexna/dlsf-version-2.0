<?php

namespace Cis\DlsfBundle\CommandBus\Admin;

use App\Entity\Dlsf\Category;
use Petroc\Component\CommandBus\HandlerInterface;
use Petroc\Component\Helper\Orm;

class EditCategoryHandler implements HandlerInterface
{
    private $orm;

    public function __construct(Orm $orm)
    {
        $this->orm = $orm;
    }

    public function handle(EditCategoryCommand $command)
    {
        $category = $command->getCategory();

        if (null === $category) {
            $category = new Category(
                $command->name,
                $command->getAcademicYear()
            );

            $this->orm->persist($category);
        } else {
            $category->setName($command->name);
            $category->setMealType($command->mealType);
        }

        $category->setInternalOnly($command->internalOnly);
    }
}
