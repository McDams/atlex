<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Gestion sécurisée des téléversements de fichiers.
 */
final class FileUpload
{
    /** @var array<string,string> Extensions images autorisées => type MIME. */
    public const IMAGE_TYPES = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
    ];

    /** @var array<string,string> Extensions documents autorisées => type MIME. */
    public const DOCUMENT_TYPES = [
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'txt'  => 'text/plain',
        'zip'  => 'application/zip',
    ];

    private string $destinationDir;

    /** @var array<string,string> */
    private array $allowedTypes;

    private int $maxSize;

    /**
     * @param array<string,string> $allowedTypes extensions => MIME
     * @param int                  $maxSize      taille max en octets
     */
    public function __construct(
        string $destinationDir,
        array $allowedTypes = self::IMAGE_TYPES,
        int $maxSize = 5_242_880
    ) {
        $this->destinationDir = rtrim($destinationDir, '/');
        $this->allowedTypes = $allowedTypes;
        $this->maxSize = $maxSize;
    }

    /**
     * Traite un fichier issu de $_FILES et retourne ses métadonnées.
     *
     * @param array<string,mixed> $file entrée de $_FILES
     * @return array{filename:string,original:string,mime:string,size:int}
     */
    public function store(array $file): array
    {
        $this->guardUploadErrors($file);

        if ($file['size'] > $this->maxSize) {
            throw new RuntimeException('Le fichier dépasse la taille maximale autorisée.');
        }

        $original = (string) $file['name'];
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));

        if (!array_key_exists($ext, $this->allowedTypes)) {
            throw new RuntimeException('Type de fichier non autorisé.');
        }

        $mime = $this->detectMime($file['tmp_name']);
        $allowedMimes = array_values($this->allowedTypes);
        if (!in_array($mime, $allowedMimes, true)) {
            throw new RuntimeException('Le contenu du fichier ne correspond pas à son extension.');
        }

        if (!is_dir($this->destinationDir)) {
            mkdir($this->destinationDir, 0o755, true);
        }

        $safeName = $this->uniqueName($original, $ext);
        $target = $this->destinationDir . '/' . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            throw new RuntimeException("Échec de l'enregistrement du fichier.");
        }

        return [
            'filename' => $safeName,
            'original' => $original,
            'mime'     => $mime,
            'size'     => (int) $file['size'],
        ];
    }

    private function guardUploadErrors(array $file): void
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException('Aucun fichier reçu.');
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Erreur lors du téléversement (code ' . $error . ').');
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('Fichier de téléversement invalide.');
        }
    }

    private function detectMime(string $tmpPath): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? (string) finfo_file($finfo, $tmpPath) : '';
        if ($finfo) {
            finfo_close($finfo);
        }

        // Les SVG sont parfois détectés comme text/plain ou text/xml.
        if (in_array($mime, ['text/plain', 'text/xml', 'application/xml'], true)
            && str_contains((string) file_get_contents($tmpPath, false, null, 0, 256), '<svg')) {
            return 'image/svg+xml';
        }

        return $mime;
    }

    private function uniqueName(string $original, string $ext): string
    {
        $base = slugify(pathinfo($original, PATHINFO_FILENAME));
        $base = substr($base, 0, 60);
        return $base . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    }
}
