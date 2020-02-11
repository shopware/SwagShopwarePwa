<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\Bundle\Helper;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class FormattingHelper
{
    public function convertToDashCase($name): string
    {
        $converter = new CamelCaseToSnakeCaseNameConverter();

        return str_replace('_', '-', $converter->normalize($name));
    }
}
