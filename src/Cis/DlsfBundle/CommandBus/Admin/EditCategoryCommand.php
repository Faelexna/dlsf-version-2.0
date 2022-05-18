<?php

namespace Cis\DlsfBundle\CommandBus\Admin;

use App\Entity\Dlsf\Category;
use App\Entity\User;
use Petroc\Component\Commandbus\Command;

class EditCategoryCommand extends Command
{
    public $name;
    public $internalOnly;
    public $mealType;
    
    private $category;
    private $academicYear;
    private $user;

    public function __construct($object, User $user)
    {
        $this->user = $user;

        if ($object instanceof Category) {
            $this->category = $object;
            $this->academicYear = $object->getAcademicYear();
            $this->mapData($object);
        } else {
            $this->academicYear = $object;
        }
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getAcademicYear()
    {
        return $this->academicYear;
    }

    public function getUser()
    {
        return $this->user;
    }
}
