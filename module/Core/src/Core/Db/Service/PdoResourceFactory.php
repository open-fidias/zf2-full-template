<?php
namespace Core\Db\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;

class PdoResourceFactory implements FactoryInterface {
    /**
    * @param ServiceLocatorInterface $serviceLocator
    * @return \PDO resource
    */
    public function createService(ServiceLocatorInterface $services) {
        $dbAdapter = $services->get('Zend\Db\Adapter\Adapter');

        $pdo = $dbAdapter->getDriver()->getConnection()->getResource();
        if (!$pdo instanceof \PDO) {
            throw new ServiceNotCreatedException('Connection resource must be an instance of PDO');
        }
        return $pdo;
    }
}
