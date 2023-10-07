<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

enum RequestMethod: string
{
    case CONNECT = 'CONNECT';
    case DELETE = 'DELETE';
    case GET = 'GET';
    case HEAD = 'HEAD';
    case OPTIONS = 'OPTIONS';
    case PATCH = 'PATCH';
    case POST = 'POST';
    case PURGE = 'PURGE';
    case PUT = 'PUT';
    case TRACE = 'TRACE';
}
