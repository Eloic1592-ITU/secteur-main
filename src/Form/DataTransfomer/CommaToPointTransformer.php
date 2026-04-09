<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CommaToPointTransformer implements DataTransformerInterface
{
    /**
     * Transforme une valeur numérique (float/string) en string pour l'affichage
     */
    public function transform($value): string
    {
        if (null === $value || '' === $value) {
            return '';
        }

        return (string) $value;
    }

    /**
     * Transforme une string (avec virgule ou point) en float
     */
    public function reverseTransform($value): ?float
    {
        if (null === $value || '' === $value) {
            return null;
        }

        // Remplacer la virgule par un point
        $value = str_replace(',', '.', $value);
        
        // Supprimer les espaces
        $value = trim($value);

        if (!is_numeric($value)) {
            throw new TransformationFailedException('Valeur numérique attendue.');
        }

        return (float) $value;
    }

    public function toFloat($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) str_replace(',', '.', $value);
    }
}