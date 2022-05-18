<?php

namespace App\Entity\Dlsf;

use App\Entity\Student\Student;
use App\Entity\User;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

class Applicant
{
    const ADVANCED_LEARNING_LOAN = 'Advanced Learning Loan';
    const FE = 'FE';
    
    const AGE_CATEGORY_UNDER_19 = 'U19';
    const AGE_CATEGORY_UNDER_19_NAME = '16-19';
    
    const AGE_CATEGORY_OVER_19 = 'O19';
    const AGE_CATEGORY_OVER_19_NAME = '19+';
    
    const AGE_CATEGORY_HE = 'HE';

    const ESA_OR_DSA_NAME = 'Universal Credit and Personal Independence Payment';
 
    const MAX_LENGTH_SORT_CODE = 6;
    const MAX_LENGTH_ACCOUNT_NUMBER = 8;

    private $id;
    private $createdOn;
    private $student;
    private $academicYear;
    private $ageCategory;
    private $age;
    private $addedByUser;
    private $careLeaver;
    private $financiallyIndependent;
    private $liveWithPartner;
    private $numberOfDependents;
    private $evidenceSeenUser;
    private $evidenceSeen;
    private $evidenceSeenDate;
    private $evidenceType;
    private $liveIndependently;
    private $incomeSupport;
    private $inCare;
    private $esaOrDsa;
    private $liveWithParent;
    private $parentBenefits;
    private $criteriaChecked;
    private $contractSigned;
    private $site;
    private $twentyFourPlusLoan;
    private $showOnEnhancedReport;
    private $householdIncome;
    private $enhancedLearner;
    private $twentyFourPlusBursary;
    private $lowIncomeEvidence;
    private $lowIncomeEvidenceOther;
    private $notOnMainstreamProgramme;
    private $twentyFourPlusEvidenceSeen;
    private $enhancedPaymentNotes;
    private $payeeName;
    private $bankAccountHolder;
    private $bankName;
    private $bankBranch;
    private $bankSortCode;
    private $bankAccountNumber;
    private $claims;
    private $notes;
    private $deletedOn;
    private $emailedBankDetails;
    private $outreachBursary;
    private $accessBursary;
    
    public function __construct(Student $student, User $user, $academicYear, $age)
    {
        $this->claims = new ArrayCollection();
        $this->notes = new ArrayCollection();
        
        $this->createdOn = new DateTime();
        $this->student = $student;
        $this->addedByUser = $user;
        $this->academicYear = $academicYear;
        $this->ageCategory = ($age < 19) ? 'U19' : 'O19';
        $this->age = $age;
        
        $this->financiallyIndependent = false;
        $this->liveWithPartner = false;
        $this->evidenceSeen = false;
        $this->liveIndependently = false;
        $this->incomeSupport = false;
        $this->inCare = false;
        $this->esaOrDsa = false;
        $this->liveWithParent = false;
        $this->criteriaChecked = false;
        $this->contractSigned = false;
        $this->twentyFourPlusLoan = false;
        $this->showOnEnhancedReport = false;
        $this->enhancedLearner = false;
        $this->twentyFourPlusBursary = false;
        $this->notOnMainstreamProgramme = false;
        $this->twentyFourPlusEvidenceSeen = false;
        $this->outreachBursary = false;
        $this->accessBursary = false;
    }
    
    public function __toString()
    {
        return 'DLSF Application ID: ' . strval($this->id) . ' - Date: ' . $this->createdOn->format('d-M-Y');
    }
    
    public function getClaims()
    {
        return $this->claims;
    }
    
    public function addClaim(Claim $claim)
    {
        $this->claims[] = $claim;
        return $this;
    }
    
    public function getNotes()
    {
            return $this->notes;
    }

    public function addNote(Note $note)
    {
            $this->notes[] = $note;
            return $this;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function isEvidenceSeen()
    {
        return true === $this->evidenceSeen;
    }

    public function getAcademicYear()
    {
        return $this->academicYear;
    }

    public function getCreatedOn()
    {
        return $this->createdOn;
    }
    
    public function getAgeCategory()
    {
        if ($this->ageCategory === self::AGE_CATEGORY_UNDER_19) {
            return self::AGE_CATEGORY_UNDER_19_NAME;
        }
        
        if ($this->ageCategory === self::AGE_CATEGORY_OVER_19) {
            return self::AGE_CATEGORY_OVER_19_NAME;  
        }
        
        return null;
    }
    
    public function isAgeCategoryOver19()
    {
        if ($this->ageCategory === self::AGE_CATEGORY_OVER_19) {
            return true;
        }
        
        return false;
    }
    
    function isTwentyFourPlusBursary()
    {
        return true === $this->twentyFourPlusBursary;
    }

    function setTwentyFourPlusBursary($twentyFourPlusBursary)
    {
        $this->twentyFourPlusBursary = $twentyFourPlusBursary;
        return $this;
    }
    
    function getFundingType()
    {
        if(true === $this->twentyFourPlusBursary) {
            return self::ADVANCED_LEARNING_LOAN;
        }
        return self::FE;
    }
    
    public function getStudent()
    {
        return $this->student;
    }

    public function getAge()
    {
        return $this->age;
    }

    public function getAddedByUser()
    {
        return $this->addedByUser;
    }

    public function isCareLeaver()
    {
        return true === $this->careLeaver;
    }

    public function isFinanciallyIndependent()
    {
        return true === $this->financiallyIndependent;
    }

    public function isLiveWithPartner()
    {
        return true === $this->liveWithPartner;
    }

    public function getNumberOfDependents()
    {
        return $this->numberOfDependents;
    }

    public function getEvidenceSeenUser()
    {
        return $this->evidenceSeenUser;
    }

    public function getEvidenceSeenDate()
    {
        return $this->evidenceSeenDate;
    }

    public function getEvidenceType()
    {
        return $this->evidenceType;
    }

    public function isLiveIndependently()
    {
        return true === $this->liveIndependently;
    }

    public function isIncomeSupport()
    {
        return true === $this->incomeSupport;
    }

    public function isInCare()
    {
        return true === $this->inCare;
    }

    public function isEsaOrDsa()
    {
        return true === $this->esaOrDsa;
    }

    public function isLiveWithParent()
    {
        return true === $this->liveWithParent;
    }

    public function getParentBenefits()
    {
        return $this->parentBenefits;
    }

    public function isCriteriaChecked()
    {
        return true === $this->criteriaChecked;
    }

    public function isContractSigned()
    {
        return true === $this->contractSigned;
    }

    public function getSite()
    {
        return $this->site;
    }

    public function isTwentyFourPlusLoan()
    {
        return true === $this->twentyFourPlusLoan;
    }

    public function isShowOnEnhancedReport()
    {
        return true === $this->showOnEnhancedReport;
    }

    public function getHouseholdIncome()
    {
        return $this->householdIncome;
    }

    public function isEnhancedLearner()
    {
        return true === $this->enhancedLearner;
    }

    public function getLowIncomeEvidence()
    {   
        return $this->lowIncomeEvidence;
    }

    public function getLowIncomeEvidenceOther()
    {
        return $this->lowIncomeEvidenceOther;
    }

    public function isNotOnMainstreamProgramme()
    {
        return true === $this->notOnMainstreamProgramme;
    }

    public function isTwentyFourPlusEvidenceSeen()
    {
        return true === $this->twentyFourPlusEvidenceSeen;
    }

    public function getEnhancedPaymentNotes()
    {
        return $this->enhancedPaymentNotes;
    }

    public function getPayeeName()
    {
        return $this->payeeName;
    }

    public function getEmailedBankDetails()
    {
        return $this->emailedBankDetails;
    }
    
    public function getBankAccountHolder()
    {
        return $this->bankAccountHolder;
    }

    public function getBankName()
    {
        return $this->bankName;
    }

    public function getBankBranch()
    {
        return $this->bankBranch;
    }

    public function getBankSortCode()
    {
        return $this->bankSortCode;
    }

    public function getBankAccountNumber()
    {
        return $this->bankAccountNumber;
    }
    
    public function getDeletedOn()
    {
            return $this->deletedOn;
    }

    public function getStudentYear()
    {
            $years = $this->student->getYears();
            foreach ($years as $year) {
                    if ($year->getAcademicYear() === $this->academicYear) {
                            return $year;
                    }
            }
    }

    public function getEnrolments()
    {
            $year = $this->getStudentYear();
            $enrolments = [];
            if (!empty($year)) {
                    foreach ($year->getEnrolments() as $enrolment) {
                            if ($enrolment->getType() === 'Q') {
                                    $enrolments[] = $enrolment;
                            }
                    }
            }
            return $enrolments;
    }

    public function isWithdrawn()
    {
            $counter = 0;
            foreach ($this->getEnrolments() as $enrolment) {
                    if ($enrolment->isWithdrawn()) {
                            $counter++;
                    }
            }
            return $counter === count($this->getEnrolments());
    }
    
    public function getLastTaughtDate()
    {
            $LTD = [];
            foreach ($this->getEnrolments() as $enrolment) {
                    if (!$enrolment->isWithdrawn()) {
                        $LTD[] = $enrolment->getExtra()->getLastTaughtDate();
                    }
            }
            if (!empty($LTD)) {
                return max($LTD);
            }
    }

    public function setStudent($student)
    {
        $this->student = $student;
        return $this;
    }

    public function setAge($age)
    {
        $this->age = $age;
        return $this;
    }
    
    public function setAgeCategory($category)
    {
        $this->ageCategory = $category;
        return $this;
    }    

    public function setAddedByUser($addedByUser)
    {
        $this->addedByUser = $addedByUser;
        return $this;
    }

    public function setCareLeaver($careLeaver)
    {
        $this->careLeaver = $careLeaver;
        return $this;
    }

    public function setFinanciallyIndependent($financiallyIndependent)
    {
        $this->financiallyIndependent = $financiallyIndependent;
        return $this;
    }

    public function setLiveWithPartner($liveWithPartner)
    {
        $this->liveWithPartner = $liveWithPartner;
        return $this;
    }

    public function setNumberOfDependents($numberOfDependents)
    {
        $this->numberOfDependents = $numberOfDependents;
        return $this;
    }

    public function setEvidenceSeenUser($evidenceSeenUser)
    {
        $this->evidenceSeenUser = $evidenceSeenUser;
        return $this;
    }

    public function setEvidenceSeen($evidenceSeen)
    {
        $this->evidenceSeen = $evidenceSeen;
        return $this;
    }

    public function setEvidenceSeenDate($evidenceSeenDate)
    {
        $this->evidenceSeenDate = $evidenceSeenDate;
        return $this;
    }

    public function setEvidenceType($evidenceType)
    {
        $this->evidenceType = $evidenceType;
        return $this;
    }

    public function setLiveIndependently($liveIndependently)
    {
        $this->liveIndependently = $liveIndependently;
        return $this;
    }

    public function setIncomeSupport($incomeSupport)
    {
        $this->incomeSupport = $incomeSupport;
        return $this;
    }

    public function setInCare($inCare)
    {
        $this->inCare = $inCare;
        return $this;
    }

    public function setEsaOrDsa($esaOrDsa)
    {
        $this->esaOrDsa = $esaOrDsa;
        return $this;
    }

    public function setLiveWithParent($liveWithParent)
    {
        $this->liveWithParent = $liveWithParent;
        return $this;
    }

    public function setParentBenefits($parentBenefits)
    {
        $this->parentBenefits = $parentBenefits;
        return $this;
    }

    public function setCriteriaChecked($criteriaChecked)
    {
        $this->criteriaChecked = $criteriaChecked;
        return $this;
    }

    public function setContractSigned($contractSigned)
    {
        $this->contractSigned = $contractSigned;
        return $this;
    }

    public function setSite($site)
    {
        $this->site = $site;
        return $this;
    }

    public function setTwentyFourPlusLoan($twentyFourPlusLoan)
    {
        $this->twentyFourPlusLoan = $twentyFourPlusLoan;
        return $this;
    }

    public function setShowOnEnhancedReport($showOnEnhancedReport)
    {
        $this->showOnEnhancedReport = $showOnEnhancedReport;
        return $this;
    }

    public function setHouseholdIncome($householdIncome)
    {
        $this->householdIncome = $householdIncome;
        return $this;
    }

    public function setEnhancedLearner($enhancedLearner)
    {
        $this->enhancedLearner = $enhancedLearner;
        return $this;
    }

    public function setLowIncomeEvidence($lowIncomeEvidence)
    {
        $this->lowIncomeEvidence = $lowIncomeEvidence;
        return $this;
    }

    public function setLowIncomeEvidenceOther($lowIncomeEvidenceOther)
    {
        $this->lowIncomeEvidenceOther = $lowIncomeEvidenceOther;
        return $this;
    }

    public function setNotOnMainstreamProgramme($notOnMainstreamProgramme)
    {
        $this->notOnMainstreamProgramme = $notOnMainstreamProgramme;
        return $this;
    }

    public function setTwentyFourPlusEvidenceSeen($twentyFourPlusEvidenceSeen)
    {
        $this->twentyFourPlusEvidenceSeen = $twentyFourPlusEvidenceSeen;
        return $this;
    }

    public function setEnhancedPaymentNotes($enhancedPaymentNotes)
    {
        $this->enhancedPaymentNotes = $enhancedPaymentNotes;
        return $this;
    }

    public function setPayeeName($payeeName)
    {
        $this->payeeName = $payeeName;
        return $this;
    }

    public function setBankAccountHolder($bankAccountHolder)
    {
        $this->bankAccountHolder = $bankAccountHolder;
        return $this;
    }

    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
        return $this;
    }

    public function setBankBranch($bankBranch)
    {
        $this->bankBranch = $bankBranch;
        return $this;
    }

    public function setBankSortCode($bankSortCode)
    {
        $this->bankSortCode = $bankSortCode;
        return $this;
    }

    public function setBankAccountNumber($bankAccountNumber)
    {
        $this->bankAccountNumber = $bankAccountNumber;
        return $this;
    }
    
    public function setOutreachBursary($outreachBursary)
    {
        $this->outreachBursary = $outreachBursary;
        return $this;
    }
    
    public function setAccessBursary($accessBursary)
    {
        $this->accessBursary = $accessBursary;
        return $this;
    }
    
    public function getOutreachBursary()
    {
        return $this->outreachBursary;
    }
    
    public function getAccessBursary()
    {
        return $this->accessBursary;
    }
    
    public function markAsDeleted()
    {
            $this->deletedOn = new DateTime();
            foreach ($this->claims as $claim) {
                    $claim->markAsDeleted();
            }
            foreach ($this->notes as $note) {
                    $note->markAsDeleted();
            }
            return $this;
    }
}
