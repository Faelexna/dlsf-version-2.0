<?php

namespace Cis\TravelBundle\Command;

use Cis\TravelBundle\Entity\AcademicYear;
use Cis\TravelBundle\Entity\Application;
use Petroc\Component\Helper\Orm;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncBursaryAmountsCommand extends Command
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
            ->setName('cis_travel:sync_bursary_amounts')
            ->setDescription('Sync Bursary Amounts')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $sites = [Application::BARNSTAPLE_SITE, Application::TIVERTON_SITE];

        $academicYears = $this->orm->getRepository(AcademicYear::class)->findCurrentAndFutureYears($this->academicYear);

        foreach($academicYears as $academicYear) {
            foreach ($sites as $site) {
                $count = 0;
                $applications = $this->orm->getRepository(Application::class)->findDlsfAprrovedApplications(
                    $academicYear->getId(),
                    $site,
                    'daily'
                );

                foreach ($applications as $application) {
                    foreach ($application->getTerms() as $term) {
                        if ($term->getNumberOfDailyCards() > 0) {
                            $claim = $term->getDlsfClaim();
                            $cost = $term->getTotalDailyCost();
                            if ($claim) {
                                if (bccomp($cost, $claim->getAmount(), 2) or bccomp($cost, $claim->getApprovedAmount(), 2) or bccomp($cost, $claim->getPaidAmount(), 2)) {
                                    $claim->setAmount($cost);
                                    $claim->setApprovedAmount($cost);
                                    $count++;
                                }
                            }
                        }
                    }
                }
                $io->success(sprintf(
                    '%s Updated: %s claims for %s.',
                    $academicYear,
                    $count,
                    $site
                ));
            }
        }
        $this->orm->flush();
        $io->success('Finished');
    }

}