<?php

namespace App\DataPersister\User;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Document\User;

class EncodedPasswordPersister implements DataPersisterInterface
{

    private $documentManager;
    private $userPasswordEncoder;

    public function __construct(DocumentManager $documentManager, UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->documentManager = $documentManager;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    public function supports($data): bool
    {
        return $data instanceof User;
    }

    public function persist($data)
    {
        if($data->getNewPassword())
        {
            $data->setPassword($this->userPasswordEncoder->encodePassword($data, $data->getNewPassword()));
            $data->eraseCredentials();
        }

        $this->documentManager->persist($data);
        $this->documentManager->flush();
    }

    public function remove($data)
    {
        $this->documentManager->remove($data);
        $this->documentManager->flush();
    }
}