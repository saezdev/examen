<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PositiveIntegerValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        /* @var PositiveInteger $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_numeric($value) || $value <= 0 || floor($value) != $value || strpos($value, '.') !== false) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
