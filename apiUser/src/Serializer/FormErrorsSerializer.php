<?php


namespace App\Serializer;


use Symfony\Component\Form\FormInterface;

class FormErrorsSerializer
{
	public function getSerializedErrorsFromForm(FormInterface $form)
	{
		$errors = [];
		foreach($form->getErrors() as $error)
		{
			$errors[] = $error->getMessage();
		}

		foreach($form->all() as $childForm)
		{
			if ($childForm instanceof FormInterface)
			{
				if ($childErrors = $this->getSerializedErrorsFromForm($childForm)) {
					$errors[$childForm->getName()] = $childErrors;
				}
			}
		}

		return $errors;
	}
}