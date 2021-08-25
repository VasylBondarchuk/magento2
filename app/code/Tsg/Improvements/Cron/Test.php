<?php

namespace Tsg\Improvements\Cron;

class Test
{
    protected $exportOrders;

    public function __construct(\Tsg\Improvements\Controller\Adminhtml\Display\Export $exportOrders)
    {
        $this->exportOrders = $exportOrders;
    }

    public function execute()
    {
        $this->exportOrders->exportOrders();
    }
}
