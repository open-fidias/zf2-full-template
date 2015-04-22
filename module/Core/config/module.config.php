<?php

namespace Core;

return array(
    'di' => array(),
    'view_helpers' => array(
        'invokables' => array(
            'session' => 'Core\View\Helper\Session'
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Log' => function ($sm) {
               $log = new Zend\Log\Logger();
               $writer = new Zend\Log\Writer\Stream('/tmp/log.txt');
               $log->addWriter($writer);

               return $log;
            },
            'PdoResource' => 'Core\Db\Service\PdoResourceFactory',
        ),
    ),
    'doctrine' => array(
        'driver' => array(
            __NAMESPACE__ . '_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                )
            )
        )
    ),
);
