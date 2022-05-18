<?php

namespace Cis\DlsfBundle\View;

use App\Entity\Dlsf\Applicant;
use Petroc\Component\Helper\Orm;

class ApplicantSummary
{
    private $orm;
    private $applicant;
    
    public function __construct(Orm $orm, Applicant $applicant)
    {
        $this->orm = $orm;
        $this->applicant = $applicant;
    }
    
    public function getApplicant()
    {
        return $this->applicant;
    }
    
    public function getClaims()
    {
        $claims = [];
        
        foreach ($this->applicant->getClaims() as $claim) {
			$claims[] = $claim;
        }
        
        return $claims;
    }
	
	public function getNotes()
	{
		$notes = [];
		
		foreach ($this->applicant->getNotes() as $note) {
			$notes[] = $note;
		}
		
		return $notes;
	}
}
