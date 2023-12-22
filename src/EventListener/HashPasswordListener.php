<?php
 
namespace App\EventListener;
 
use App\Entity\AdminUser;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
 
#[AsDoctrineListener(Events::prePersist)]
#[AsDoctrineListener(Events::preUpdate)]
class HashPasswordListener
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }
 
    public function prePersist(PrePersistEventArgs $args): void
    {
        $this->setPassword($args);
    }
 
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $this->setPassword($args);
    }
 
    private function setPassword(PrePersistEventArgs|PreUpdateEventArgs $args): void {
        $entity = $args->getObject();
        if (!$entity instanceof AdminUser) {
            return;
        }
 
        if (empty($entity->getPlainPassword())) {
            dump($entity);
            // return;
        }
 
        $entity->setPassword($this->passwordHasher->hashPassword($entity, $entity->getPlainPassword()));
        $entity->setPlainPassword(null);
    }
}
 