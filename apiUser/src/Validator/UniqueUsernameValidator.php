<?php

namespace App\Validator;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use App\Document\User;

class UniqueUsernameValidator extends ConstraintValidator
{
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\User\UniqueUsername */

        if (null === $value->getUsername() || '' === $value->getUsername()) {
            return;
        }

        $alreadyExist = $this->dm->getRepository(User::class)->findOneBy(['username'=>$value->getUsername()]);

        if(empty($alreadyExist)) return;

        if($alreadyExist->getId() === $value->getId()) return;

		$this->context->buildViolation($constraint->message)
			->setParameter('{{ value }}', $value->getUsername())
			->atPath('username')
			->addViolation();
    }
}
