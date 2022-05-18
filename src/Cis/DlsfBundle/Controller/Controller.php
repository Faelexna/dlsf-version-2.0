<?php

namespace Cis\DlsfBundle\Controller;

use Petroc\Component\View\ViewTrait;
use Petroc\Component\Helper\Orm;
use Petroc\Component\Helper\Security;

class Controller
{
    use ViewTrait;
	
	const ADMIN_ACCESS_RULE = 'cis_dlsf.admin';
	const FINANCE_ACCESS_RULE = 'cis_dlsf.finance';
	
	const APPLICANT_VIEW_ROUTE = 'cis_dlsf.applicant.view';
	const APPLICANT_EDIT_ROUTE = 'cis_dlsf.applicant.edit';

    const ADMIN_INDEX_ROUTE = 'cis_dlsf.admin';

    const FINANCE_INDEX_ROUTE = 'cis_dlsf.finance';
    
    protected $orm;
	protected $security;
    
    public function __construct(Orm $orm, Security $security)
    {
        $this->orm = $orm;
		$this->security = $security;
    }
}
