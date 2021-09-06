<?php

declare(strict_types = 1);

namespace Tsg\Improvements;

use Tsg\Improvements\Helper\Email;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

/**
 *
 */
class FailureEmailDetails
{
    /**
     * @var Email
     */
    protected $email;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @param Email $email
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlInterface
     */
    public function __construct(
        Email $email,
        StoreManagerInterface $storeManager,
        UrlInterface $urlInterface
    ) {
        $this->email = $email;
        $this->storeManager = $storeManager;
        $this->urlInterface = $urlInterface;
    }

    /**
     * @param array $emailDetailsNames
     * @param array $emailDetailsValues
     * @return array
     */
    public function getEmailDetails(array $emailDetailsNames, array $emailDetailsValues) : array
    {
        return array_combine($emailDetailsNames, $emailDetailsValues);
    }

    /**
     * @param array $senderDetailsValues
     * @return array
     */
    public function getSenderDetails(array $senderDetailsValues) : array
    {
        return $this->getEmailDetails(['name','email'], $senderDetailsValues);
    }

    /**
     * @param array $templateVarValues
     * @return array
     */
    public function getTemplateVars(array $templateVarValues) : array
    {
        return $this->getEmailDetails(['recipientName', 'link', 'senderName', 'reason'], $templateVarValues);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getTemplateOptions() : array
    {
        return $this->getEmailDetails(
            ['area', 'store'],
            [\Magento\Framework\App\Area::AREA_ADMINHTML, $this->storeManager->getStore()->getId()]
        );
    }

    /**
     * @param $recipientEmail
     * @return string
     */
    public function getRecipientEmail($recipientEmail) : string
    {
        return $recipientEmail;
    }

    /**
     * @param $templateIdentifier
     * @return string
     */
    public function getTemplateIdentifier($templateIdentifier) : string
    {
        return $templateIdentifier;
    }

    /**
     * @param string $pathFromBaseUrl
     * @return string
     */
    public function getLink($pathFromBaseUrl = "") : string
    {
        return $this->urlInterface->getBaseUrl().$pathFromBaseUrl;
    }

    /**
     * @param $senderDetails
     * @param $recipientEmail
     * @param $templateIdentifier
     * @param $templateOptions
     * @param $templateVars
     */
    public function sendFailureEmail($senderDetails, $recipientEmail, $templateIdentifier, $templateOptions, $templateVars)
    {
        $this->email->sendEmail($senderDetails, $recipientEmail, $templateIdentifier, $templateOptions, $templateVars);
    }
}
