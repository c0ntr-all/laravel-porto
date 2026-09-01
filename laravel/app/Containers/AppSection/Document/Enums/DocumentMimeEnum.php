<?php declare(strict_types=1);

namespace App\Containers\AppSection\Document\Enums;

enum DocumentMimeEnum: string
{
    case PDF = 'application/pdf';
    case DOC = 'application/msword';
    case DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    case ZIP = 'application/zip';
    case ZIP_COMPRESSED = 'application/x-zip-compressed';
    case TXT = 'text/plain';
    case XLS = 'application/vnd.ms-excel';
    case XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    case PPT = 'application/vnd.ms-powerpoint';
    case PPTX = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    case RAR = 'application/vnd.rar';
    case SEVEN_ZIP = 'application/x-7z-compressed';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
