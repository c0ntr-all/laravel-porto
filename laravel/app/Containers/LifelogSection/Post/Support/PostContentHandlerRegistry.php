<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Support;

use App\Containers\LifelogSection\Post\Contracts\PostContentHandlerInterface;
use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Containers\LifelogSection\Post\Handlers\MoviePostContentHandler;
use Illuminate\Support\Collection;

class PostContentHandlerRegistry
{
    /** @var list<PostContentHandlerInterface> */
    private array $handlers;

    public function __construct(
        MoviePostContentHandler $moviePostContentHandler,
    ) {
        $this->handlers = [
            $moviePostContentHandler,
        ];
    }

    public function for(PostContentTypeEnum $contentType): ?PostContentHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($contentType)) {
                return $handler;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public function eagerLoadRelationsFor(?PostContentTypeEnum $contentType = null): array
    {
        $handlers = $contentType
            ? array_filter([$this->for($contentType)])
            : $this->handlers;

        return Collection::make($handlers)
            ->flatMap(fn (PostContentHandlerInterface $handler) => $handler->eagerLoadRelations())
            ->unique()
            ->values()
            ->all();
    }
}
