<?php declare(strict_types=1);

namespace App\Ship\Packages\VideoMetadata\Contracts;

use FFMpeg\FFProbe\DataMapping\Stream;

interface VideoMetadataStrategyContract
{
    /**
     * Получить длительность видео в секундах
     *
     * @param Stream $stream
     * @return string|null Длительность в секундах или null если не удалось определить
     */
    public function getDuration(Stream $stream): ?string;

    /**
     * Получить ширину видео
     *
     * @param Stream $stream
     * @return int|null
     */
    public function getWidth(Stream $stream): ?int;

    /**
     * Получить высоту видео
     *
     * @param Stream $stream
     * @return int|null
     */
    public function getHeight(Stream $stream): ?int;

    /**
     * Получить битрейт видео в битах в секунду
     *
     * @param Stream $stream
     * @return string|null Битрейт или null если не удалось определить
     */
    public function getBitrate(Stream $stream): ?string;

    /**
     * Получить фреймрейт видео
     *
     * @param Stream $stream
     * @return string|null Битрейт или null если не удалось определить
     */
    public function getFramerate(Stream $stream): ?string;

    /**
     * Получить соотношение сторон видео
     *
     * @param Stream $stream
     * @return string|null
     */
    public function getAspectRatio(Stream $stream): ?string;

    /**
     * Получить название кодека
     *
     * @param Stream $stream
     * @return string|null
     */
    public function getCodecName(Stream $stream): ?string;
}
