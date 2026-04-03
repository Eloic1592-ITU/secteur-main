<?php
namespace App\EventListener;

use App\Entity\OrigineValide;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

class TimestampListener
{
    public function preUpdate(OrigineValide $entity, PreUpdateEventArgs $event): void
    {
        $entity->setUpdatedAt(new \DateTime());
    }
}
