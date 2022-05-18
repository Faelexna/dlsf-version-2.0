<?php

namespace Cis\TravelBundle\Command;

use App\Entity\Dlsf\Applicant;
use App\Entity\Dlsf\Category;
use App\Entity\Dlsf\Claim;
use App\Entity\User;
use Cis\TravelBundle\Entity\Application;
use Cis\TravelBundle\Entity\Card;
use Cis\TravelBundle\Entity\Contractor;
use InvalidArgumentException;
use Petroc\Component\Helper\Orm;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AttachAndCreateBursaryApplicationCommand extends Command 
{
    private $orm;
    private $academicYear;
    
    public function __construct(Orm $orm, $academicYear) 
    {
        $this->orm = $orm;
        $this->academicYear = $academicYear;
        parent::__construct();
    }
    
    protected function configure() 
    {
        $this
            ->setName('cis_travel:attach_and_create_bursary_application')
            ->setDescription('Attach and Create Bursary Application')
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output); 
        $io->title($this->getDescription());
        
        $orm = $this->orm;
        $academicYear = $this->academicYear;
        
        $category = $this->orm->getRepository(Category::class)->findOneBy(['academicYear' => $academicYear,'name' => Category::TRAVEL_CATEGORY]);
        
        if($category === null) {
            throw new InvalidArgumentException('Cannot find DLSF category '.Category::TRAVEL_CATEGORY.' for '.$academicYear);
        };
        
        $cisUser = $orm->getRepository(User::class)->findOneById(300010179);
        
        if($cisUser === null) {
            throw new InvalidArgumentException('CIS user not found.');
        };
        
        $applications = $this->getApplicationAmounts();
        
        $count = count($applications);
        
        if($count < 1) {
            $io->success('None to attach and create.');
            exit;
        }
        
        $io->comment(sprintf(
           '%s to attach and create.',
            $count
        ));
            
        foreach($applications as $application) {
            
            $travel = $application['travel_application'];
            $cost = $application['cost'];
            
            $claim = new Claim(
                    $application['dlsf_applicant'],
                    $category,
                    $cisUser
                );

            $claim->setAmount($cost);
            $claim->approve($cost,$cisUser);
            $claim->setNotes('Termly Ticket');
            $claim->setPaymentType(Claim::PAYMENT_TYPE_BUDGET_TRANSFER);
            $claim->setAddedByTravelSystem(true);
            $orm->persist($claim);
            
            $travel->setDlsfClaim($claim);
            
            $io->success(sprintf(
               'Attached to %s and created claim %s.',
                $travel->getId(),
                $claim->getId()
            ));
        }   
        
        $orm->flush();
        $io->success('Finished');
    }
    
    private function getApplicationAmounts() 
    {
        $academicYear = $this->academicYear;
        $orm = $this->orm;
        
        $sql = 'SELECT
                    d.PETROC_TRAVEL_APPLICATION,
                    d.STUDENT,
                    d.ACADEMIC_YEAR,
                    d.PETROC_DLSF_APPLICANT,
                    SUM(ptt.RATE) TRAVEL_COST
                FROM
                    (SELECT
                        COUNT(pda.OBJECT_ID) NUMBER_OF_APPLICATIONS,
                        d.PETROC_TRAVEL_APPLICATION,
                        d.STUDENT,
                        d.ACADEMIC_YEAR,
                        STRING_AGG(pda.OBJECT_ID, \', \') PETROC_DLSF_APPLICANT
                    FROM
                        (SELECT DISTINCT
                            pta.OBJECT_ID PETROC_TRAVEL_APPLICATION,
                            pta.STUDENT,
                            pta.ACADEMIC_YEAR
                        FROM
                            PETROC_TRAVEL_APPLICATION pta
                        JOIN PETROC_TRAVEL_TERM ptt ON pta.OBJECT_ID = ptt.PETROC_TRAVEL_APPLICATION
                        JOIN PETROC_TRAVEL_CARD ptc ON ptt.OBJECT_ID = ptc.PETROC_TRAVEL_TERM AND ptc.DELETED_ON IS NULL AND ptc.PETROC_TRAVEL_CONTRACTOR IN (?,?,?,?,?) AND ptc.STATUS NOT IN (?, ?)
                        WHERE
                            pta.ACADEMIC_YEAR = ?
                        AND pta.STATUS = ?
                        AND pta.PETROC_DLSF_CLAIM IS NULL
                        GROUP BY
                            pta.OBJECT_ID,
                            pta.STUDENT,
                            pta.ACADEMIC_YEAR) d
                    JOIN PETROC_DLSF_APPLICANT pda ON d.STUDENT = pda.STUDENT AND d.ACADEMIC_YEAR = pda.ACADEMIC_YEAR AND pda.EVIDENCE_SEEN = 1 AND pda.DELETED_ON IS NULL
                    GROUP BY
                        d.PETROC_TRAVEL_APPLICATION,
                        d.STUDENT,
                        d.ACADEMIC_YEAR
                    HAVING 
                        COUNT(pda.OBJECT_ID) = 1) d
                JOIN PETROC_TRAVEL_TERM ptt ON d.PETROC_TRAVEL_APPLICATION = ptt.PETROC_TRAVEL_APPLICATION
                GROUP BY
                    d.PETROC_TRAVEL_APPLICATION,
                    d.STUDENT,
                    d.ACADEMIC_YEAR,
                    d.PETROC_DLSF_APPLICANT'
        ;

        $results =
            $orm->getConnection()->fetchAll(
            $sql, 
            [Contractor::DEVON_COUNTY_COUNCIL_ENTITLED_ID,
            Contractor::DEVON_COUNTY_COUNCIL_NON_ENTITLED_ID,
            Contractor::TRAIN_ID,
            Contractor::CORNWALL_COUNTY_COUNCIL_ID,
            Contractor::STAGECOACH_TERMRIDER_BARNSTAPLE_ID,
            Card::NEVER_ISSUED_STATUS,
            Card::WITHDRAWN_STATUS,
            $academicYear,
            Application::BURSARY_APPROVED_STATUS
            ]
        );
        
        
        $applications = [];

        foreach($results as $result) {
            $travelApplication = $orm->getRepository(Application::class)->findOneById($result['PETROC_TRAVEL_APPLICATION']);
            $dlsfApplicant = $orm->getRepository(Applicant::class)->findOneById($result['PETROC_DLSF_APPLICANT']);
            $cost = $result['TRAVEL_COST'];
            if($travelApplication and $dlsfApplicant and $cost > 0) {
                $applications[] = [
                    'travel_application' => $travelApplication,
                    'dlsf_applicant' => $dlsfApplicant,
                    'cost' => $cost
                ];
            }
        }
        
        return $applications;
    }
}