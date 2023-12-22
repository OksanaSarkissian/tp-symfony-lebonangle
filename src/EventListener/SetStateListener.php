<?php
 
namespace App\EventListener;
 
use App\Entity\Advert;
use App\Model\StateEnum;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
 
#[AsDoctrineListener(Events::prePersist)]
class SetStateListener
{
    public function __construct()
    {
    }
 
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
 
        if (!$entity instanceof Advert) {
            return;
        }
 
        $entity->setState('Draft');
    }
}
 