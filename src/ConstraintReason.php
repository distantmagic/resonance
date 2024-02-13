<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum ConstraintReason: string
{
    case FileNotFound = 'file_not_found';
    case FileNotReadable = 'file_not_readable';
    case InvalidDataType = 'invalid_data_type';
    case InvalidEnumValue = 'invalid_enum_value';
    case InvalidFormat = 'invalid_format';
    case InvalidNestedConstraint = 'invalid_nested_constraint';
    case MissingProperty = 'missing_property';
    case Ok = 'ok';
    case UnexpectedProperty = 'unexpected_property';
}
