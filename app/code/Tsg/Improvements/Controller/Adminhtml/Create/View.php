<?php

declare(strict_types = 1);

namespace Tsg\Improvements\Controller\Adminhtml\Create;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class View extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;

    public function __construct(Context $context,PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Log Files Viewer'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Tsg_Improvements::menu');
    }
}


