<?php

namespace App\Validator;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use App\Document\User;

class UniqueEmailValidator extends ConstraintValidator
{

    private $dm;

    public function __construct(DocumentManager $documentManager)
    {
        $this->dm = $documentManager;
    }

    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\User\UniqueEmail */

        if (null === $value->getEmail() || '' === $value->getEmail()) {
            return;
        }

        $alreadyExist = $this->dm->getRepository(User::class)->findOneBy(['email'=>$value->getEmail()]);

		if(empty($alreadyExist)) return;

		if($alreadyExist->getId() === $value->getId()) return;

		$this->context->buildViolation($constraint->message)
			->setParameter('{{ value }}', $value->getEmail())
			->atPath('email')
			->addViolation();
    }
}
