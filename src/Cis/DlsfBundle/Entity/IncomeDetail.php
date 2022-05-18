<?php

namespace Cis\DlsfBundle\Entity;

use DateTime;
use App\Entity\Dlsf\Category;
use Cis\FinanceBundle\Model\TransferDetail;

class IncomeDetail extends TransferDetail
{
    const CREDITOR_COST_CENTRE = 'ZN05-9640';

    private $id;
    private $createdOn;
    private $category;

    public function __construct(Income $income, $costCentre, $description, $amount, Category $category)
    {
        parent::_construct($income, $costCentre, $this::CREDITOR_COST_CENTRE, $description, $amount);
        $this->createdOn = new DateTime();
        $this->category = $category;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
    }
}
