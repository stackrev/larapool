<?php

namespace MstGhi\Larapool\Contracts;

use Closure;
use SoapClient as MainSoapClient;

class SoapClient
{
    private $soap;

    protected $config;

    protected $attempts;

    public function __construct($soapServer, $config, $options = array())
    {
        $this->config = $config;

        $this->attempts = (int) $this->config->get('soap.attempts');

        $this->attempt($this->attempts, function() use($soapServer, $options) {
            $this->makeSoapServer($soapServer, $options);
        });
    }

    protected function attempt($attempts, Closure $statements)
    {
        do {
            try {
                return $statements();
            } catch(\Exception $e) {
                $attempts--;

                if ($attempts == 0)
                    throw $e;
            }
        } while(true);
    }

    protected function makeSoapServer($soapServer, $options)
    {
        $this->soap = new MainSoapClient($soapServer, $options);
    }

    public function __call($name, array $arguments)
    {
        return $this->attempt($this->attempts, function() use($name, $arguments) {
            return call_user_func_array([$this->soap, $name], $arguments);
        });
    }
}
