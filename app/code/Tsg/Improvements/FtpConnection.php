<?php

declare(strict_types = 1);

namespace Tsg\Improvements;

use Magento\Framework\Filesystem\Io\Ftp;
use Tsg\Improvements\Model\ImportCsvConfigs;
use Tsg\Improvements\FtpConnFailureEmail;

/**
 *
 */
class FtpConnection
{
    /**
     * @var Ftp
     */
    private $ftp;
    /**
     * @var \Tsg\Improvements\FtpConnFailureEmail
     */
    private $ftpConnFailureEmail;
    /**
     * @var
     */
    public $ftpConnFailureReason;
    /**
     * @var ImportCsvConfigs
     */
    private $configs;

    /**
     * @param Ftp $ftp
     * @param \Tsg\Improvements\FtpConnFailureEmail $ftpConnFailureEmail
     * @param ImportCsvConfigs $configs
     */
    public function __construct(
        Ftp $ftp,
        FtpConnFailureEmail $ftpConnFailureEmail,
        ImportCsvConfigs $configs

    ) {
        $this->ftp = $ftp;
        $this->ftpConnFailureEmail = $ftpConnFailureEmail;
        $this->configs = $configs;
    }

    /**
     * @return array
     */
    public function getFtpDetails() : array
    {
        $ftpConnDetails =
            [
                'host' => $this->configs->getFtpHost(),
                'user' => $this->configs->getFtpUserName(),
                'password' => $this->configs->getFtpUserPass(),
                'ssl' => false,
                'passive' => false
            ];

        return $ftpConnDetails;
    }

    /**
     * @return bool|true
     */
    public function ftpConnection() : bool
    {
        $connection = false;

        try {
            $connection = $this->ftp->open($this->getFtpDetails());
        } catch (\Exception $e) {
            $this->ftpConnFailureReason = $e->getMessage();
        }
        return $connection;
    }

    /**
     * @return bool
     */
    public function isConnSuccessful() : bool
    {
        $numberOfAttempts = $this->configs->getNumberOfFtpAttempts();

        for ($i = 0; $i < $numberOfAttempts; $i++) {
            if ($this->ftpConnection()) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     */
    public function sendFtpConnFailureEmail() : void
    {
        $this->ftpConnFailureEmail->sendFtpConnFailureEmail($this->ftpConnFailureReason);
    }
}
