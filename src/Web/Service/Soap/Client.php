<?php

namespace AstralWeb\LibFedex\Web\Service\Soap;

class Client
{
    protected $_credentialKey = '';
    protected $_credentialPassword = '';
    protected $_accountNumber = '';
    protected $_meterNumber = '';


    public function __construct(
        $credentialKey,
        $credentialPassword,
        $accountNumber,
        $meterNumber)
    {
        $this->_credentialKey = $credentialKey;
        $this->_credentialPassword = $credentialPassword;
        $this->_accountNumber = $accountNumber;
        $this->_meterNumber = $meterNumber;
    }

    /**
     * @return string
     */
    public function getCredentialKey()
    {
        return $this->_credentialKey;
    }

    /**
     * @return string
     */
    public function getCredentialPassword()
    {
        return $this->_credentialPassword;
    }

    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->_accountNumber;
    }

    /**
     * @return int
     */
    public function getMeterNumber()
    {
        return $this->_meterNumber;
    }
}
