<?php

declare(strict_types = 1);

namespace Tsg\Improvements\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ImportCsvConfigs
{
    const FTP_HOST = 'improvements/general/ftp_host';
    const FTP_USER_NAME = 'improvements/general/user_name';
    const FTP_USER_PASS = 'improvements/general/user_password';
    const FTP_CONN_ATTEMPTS = 'improvements/general/connection_attempts';
    const DEFAULT_FTP_CONN_ATTEMPTS = 5;
    const DEVELOPER_NAME = 'TSG';
    const DEVELOPER_EMAIL = 'office@transoftgroup.com';
    const CUSTOMER_NAME = 'Test Company';
    const CUSTOMER_EMAIL = 'improvements/general/customer_email';
    const FTP_IMPORT_DIR = 'improvements/general/ftp_import_dir';
    const LOCAL_IMPORT_DIR = BP . DS . 'pub' . DS . 'media' . DS . 'import';

     /**
     * @param ScopeConfigInterface $scopeConfig
     */

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string $configPath
     * @return string|null
     */
    public function getConfigs(string $configPath): ?string
    {
        return $this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getFtpHost(): string
    {
        return $this->getConfigs(self::FTP_HOST);
    }

    /**
     * @return string
     */
    public function getFtpUserName(): string
    {
        return $this->getConfigs(self::FTP_USER_NAME);
    }

    /**
     * @return string
     */
    public function getFtpUserPass(): string
    {
        return $this->getConfigs(self::FTP_USER_PASS);
    }

    /**
     * @return string
     */
    public function getConnAttempts(): string
    {
        return $this->getConfigs(self::FTP_CONN_ATTEMPTS);
    }

    public function getNumberOfFtpAttempts()
    {
        return ($this->getConfigs(self::FTP_CONN_ATTEMPTS) > 0)
            ? $this->getConfigs(self::FTP_CONN_ATTEMPTS)
            : self::DEFAULT_FTP_CONN_ATTEMPTS;
    }

    public function getCustomerEmail() : string
    {
        return $this->getConfigs(self::CUSTOMER_EMAIL);
    }


    /**
     * @return string
     */
    public function getFtpImportPath() : string
    {
        return $this->getConfigs(self::FTP_IMPORT_DIR). DS . "import.csv";
    }

    /**
     * @return string
     */
    public function getLocalImportPath() : string
    {
        return self::LOCAL_IMPORT_DIR . DS ."import.csv";
    }
}
