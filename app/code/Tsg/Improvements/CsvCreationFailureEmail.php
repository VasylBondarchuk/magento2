<?php

declare(strict_types = 1);

namespace Tsg\Improvements;

use Tsg\Improvements\Model\ImportCsvConfigs;
use Tsg\Improvements\FailureEmailDetails;

class CsvCreationFailureEmail
{
    private $configs;
    private $failureEmailDetails;

    /**
     * @param $configs
     * @param $failureEmailDetails
     */
    public function __construct(ImportCsvConfigs $configs, FailureEmailDetails $failureEmailDetails)
    {
        $this->configs = $configs;
        $this->failureEmailDetails = $failureEmailDetails;
    }

    public function sendCsvCreationFailureEmail($failureReason)
    {
        $this->failureEmailDetails->sendFailureEmail(
            $this->failureEmailDetails->getSenderDetails([$this->configs::DEVELOPER_NAME,$this->configs::DEVELOPER_EMAIL]),
            $this->failureEmailDetails->getRecipientEmail($this->configs::DEVELOPER_EMAIL),
            $this->failureEmailDetails->getTemplateIdentifier('email_csv_creation_failure_template'),
            $this->failureEmailDetails->getTemplateOptions(),
            $this->failureEmailDetails->getTemplateVars(
                [$this->configs::DEVELOPER_NAME,
                    $this->failureEmailDetails->getLink('admin/'),
                    $this->configs::DEVELOPER_NAME,
                    $failureReason
                ])
        );
    }
}
