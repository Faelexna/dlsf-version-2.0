<?php

namespace Cis\DlsfBundle\Controller;

use App\Entity\Dlsf\Applicant;
use App\Entity\Dlsf\Claim;
use App\Entity\Dlsf\Note;
use Cis\DlsfBundle\CommandBus\Applicant as Command;
use Cis\DlsfBundle\Form\Applicant as Form;
use Cis\DlsfBundle\View\ApplicantSummary;
use Petroc\Component\View\RouteRedirect;
use Symfony\Component\Security\Core\User\UserInterface;

class ApplicantController extends Controller
{
        public function indexAction($academicYear)
	{
		$command = new Command\SearchApplicantCommand();
		$command->academicYear = $academicYear;
		
		return $this
			->createFormView(Form\SearchApplicantFormType::class, $command)
			->restrictTo(self::ADMIN_ACCESS_RULE)
			->setTemplateData([
				'academicYear' => $academicYear
			])
			->setData($command, 'filter')
		;
	}
        
	public function viewAction(Applicant $applicant)
	{
		$academicYear = $applicant->getAcademicYear();
		
		return $this
			->createView()
			->setData(new ApplicantSummary($this->orm,$applicant), 'summary')
			->restrictTo(self::ADMIN_ACCESS_RULE)
		;
	}
	
	public function editAction(Applicant $applicant)
	{
		$academicYear = $applicant->getAcademicYear();
		$command = new Command\EditApplicantCommand($applicant, $this->security->getUser());
		
		return $this
			->createFormView(Form\EditApplicantFormType::class, $command)
			->restrictTo(Self::ADMIN_ACCESS_RULE)
			->setData($applicant, 'applicant')
			->setTemplateData([
				'academicYear' => $academicYear
			])
			->onSuccessRoute(Self::APPLICANT_VIEW_ROUTE, $applicant)
		;
	}
	
	
	public function addClaimAction(Applicant $applicant)
	{
		$academicYear = $applicant->getAcademicYear();
		$command = new Command\EditClaimCommand($applicant, $this->security->getUser());
		
		return $this
			->createFormView(Form\EditClaimFormType::class, $command)
			->restrictTo(Self::ADMIN_ACCESS_RULE)
			->setData($applicant, 'applicant')
			->setTemplateData(['academicYear' => $academicYear])
			->onSuccessRoute(Self::APPLICANT_EDIT_ROUTE, $applicant)
		;
	}
	
	public function editClaimAction(Claim $claim, Applicant $applicant)
	{
		$academicYear = $applicant->getAcademicYear();
		$command = new Command\EditClaimCommand($claim, $this->security->getUser());
		
		return $this
			->createFormView(Form\EditClaimFormType::class, $command)
			->restrictTo(Self::ADMIN_ACCESS_RULE)
			->setData($claim, 'claim')
			->setTemplateData(['academic_year' => $academicYear])
			->onSuccessRoute(Self::APPLICANT_EDIT_ROUTE, $applicant)
		;
	}

	public function editNoteAction(Note $note)
	{
		$applicant = $note->getApplicant();
		$command = new Command\EditNoteCommand($note);

		return $this
			->createFormView(Form\EditNoteFormType::class, $command)
			->restrictTo(self::ADMIN_ACCESS_RULE)
			->setData($note, 'note')
			->onSuccessRoute(Self::APPLICANT_EDIT_ROUTE, $applicant)
		;
	}
	
	public function addNoteAction(Applicant $applicant)
	{
		$academicYear = $applicant->getAcademicYear();
		$command = new Command\EditNoteCommand($applicant, $this->security->getUser());
		
		return $this
			->createFormView(Form\EditNoteFormType::class, $command)
			->restrictTo(self::ADMIN_ACCESS_RULE)
			->setData($applicant, 'applicant')
			->setTemplateData(['academicYear' => $academicYear])
			->onSuccessRoute(Self::APPLICANT_EDIT_ROUTE, $applicant)
		;
	}

}
