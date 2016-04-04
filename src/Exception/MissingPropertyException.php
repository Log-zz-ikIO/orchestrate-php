<?php
namespace andrefelipe\Orchestrate\Exception;

class MissingPropertyException extends \RuntimeException
{
    public function __construct($property, $setter = '')
    {
        $msg = 'Missing required property "'.$property.'".';
        if ($setter) {
            $msg .= ' You may fulfill with the "'.$setter.'" method.';
        }
        parent::__construct($msg);
    }
}
