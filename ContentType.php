<?php

declare(strict_types=1);

namespace Resonance;

enum ContentType: string
{
    case ApplicationJson = 'application/json';
    case ApplicationOctetStream = 'application/octet-stream';
    case FontTtf = 'font/ttf';
    case ImageJpeg = 'image/jpeg';
    case ImageSvg = 'image/svg+xml';
    case TextCss = 'text/css';
    case TextHtml = 'text/html';
    case TextJavaScript = 'text/javascript';
    case TextPlain = 'text/plain';
}
