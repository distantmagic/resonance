<?php

declare(strict_types=1);

namespace Distantmagic\Resonance;

use Distantmagic\Resonance\Attribute\Singleton;
use League\CommonMark\Extension\Embed\Embed;
use League\CommonMark\Extension\Embed\EmbedAdapterInterface;
use League\CommonMark\Util\HtmlElement;

#[Singleton]
readonly class CommonMarkEmbedAdapter implements EmbedAdapterInterface
{
    private const PATTERN = '(?:.+?)?(?:\/v\/|watch\/|\?v=|\&v=|youtu\.be\/|\/v=|^youtu\.be\/|watch\%3Fv\%3D)([a-zA-Z0-9_-]{11})+?';

    public function getHtmlElement(Embed $embed): HtmlElement
    {
        return new HtmlElement(
            'iframe',
            [
                'src' => 'https://www.youtube-nocookie.com/embed/'.$this->getId($embed->getUrl()),
                'frameborder' => '0',
                'allow' => 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture',
                'allowfullscreen' => '',
            ],
        );
    }

    public function updateEmbeds(array $embeds): void
    {
        foreach ($embeds as $embed) {
            $embed->setEmbedCode((string) $this->getHtmlElement($embed));
        }
    }

    protected function getId(string $url): string
    {
        preg_match('/'.self::PATTERN.'/', $url, $matches);

        return $matches[1] ?? '';
    }
}
