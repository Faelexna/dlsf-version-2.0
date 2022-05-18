<?php

namespace Cis\DlsfBundle\Controller;

use App\Entity\Dlsf\Category;
use App\Entity\Dlsf\EvidenceType;
use Cis\DlsfBundle\View\AdminSummary;
use Cis\DlsfBundle\CommandBus\Admin as Command;
use Cis\DlsfBundle\Entity\PaymentRequest;
use Cis\DlsfBundle\Form\Admin as Form;

class AdminController extends Controller
{
	public function indexAction($academicYear)
	{
		return $this
			->createView()
			->setData(new AdminSummary($this->orm,$academicYear), 'summary')
			//->restrictTo(self::ADMIN_ACCESS_RULE)
		;
	}

	public function addCategoryAction($academicYear)
	{
		$command = new Command\EditCategoryCommand($academicYear, $this->security->getUser());

		return $this
			->createFormView(Form\EditCategoryFormType::class, $command)
			//->restrictTo(self::ADMIN_ACCESS_RULE)
			->setTemplateData(['academicYear' => $academicYear])
			->onSuccessRoute(self::ADMIN_INDEX_ROUTE, $academicYear)
		;
	}

	public function editCategoryAction(Category $category)
	{
		$command = new Command\EditCategoryCommand($category, $this->security->getUser());

		return $this
			->createFormView(Form\EditCategoryFormType::class, $command)
			//->restrictTo(self::ADMIN_ACCESS_RULE)
			->setTemplateData(['academicYear' => $category->getAcademicYear()])
			->setData($category, 'category')
			->onSuccessRoute(self::ADMIN_INDEX_ROUTE)
		;
	}

	public function addEvidenceTypeAction($academicYear)
	{
		$command = new Command\EditEvidenceTypeCommand($academicYear, $this->security->getUser());

		return $this
			->createFormView(Form\EditEvidenceTypeFormType::class, $command)
			//->restrictTo(self::ADMIN_ACCESS_RULE)
			->setTemplateData(['academicYear' => $academicYear])
			->onSuccessRoute(self::ADMIN_INDEX_ROUTE, $academicYear)
		;
	}

	public function editEvidenceTypeAction(EvidenceType $evidenceType)
	{
		$command = new Command\EditEvidenceTypeCommand($evidenceType, $this->security->getUser());

		return $this
			->createFormView(Form\EditEvidenceTypeFormType::class, $command)
			//->restrictTo(self::ADMIN_ACCESS_RULE)
			->setTemplateData(['academicYear' => $evidenceType->getAcademicYear()])
			->setData($evidenceType, 'evidenceType')
			->onSuccessRoute(self::ADMIN_INDEX_ROUTE)
		;
	}

	public function paymentRequestFilterAction($academicYear)
	{
		$command = new Command\FilterCommand(
			$academicYear,
			Command\FilterCommand::PRE_FILTER_PAYMENT
		);
		
		$description = 'Dlsf Payment Request';

		//$paymentRequest = new PaymentRequest($this->security->getUser(), $description, $academicYear);

		return $this
			->createFormView(Form\FilterFormType::class, $command)
			->setTemplateData([
				'academicYear' => $academicYear
			])
			->setData($command, 'filter')
		;
	}

	public function transferRequestFilterAction($academicYear)
	{
		$command = new Command\FilterCommand(
			$academicYear,
			Command\FilterCommand::PRE_FILTER_TRANSFER
		);

		return $this
			->createFormView(Form\FilterFormType::class)
			->setTemplateData([
				'academicYear' => $academicYear
			])
			->setData($command, 'filter')
		;
	}
	
}