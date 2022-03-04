<?php

namespace Myparcelasia\Shipping\Logger\Api\Myparcelasia;

use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Logger\Monolog;

class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Monolog::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/mpa-api.log';
}