<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUtilities
{
    private string $uploadsDirectory;

    public function __construct(string $uploadsDirectory)
    {
        $this->uploadsDirectory = $uploadsDirectory;
    }

    /**
     * Sube una imagen al directorio especificado y retorna el nombre del archivo.
     *
     * @param UploadedFile $file
     * @param string|null $newFilename
     * @return string
     * @throws FileException
     */
    public function uploadImage(UploadedFile $file, string $newFilename = null): string
    {
        // Generar un nombre único si no se proporciona uno
        if (!$newFilename) {
            $newFilename =  uniqid() . '-' . $file->getClientOriginalName();
        }
        // Mover el archivo al directorio de subidas
        try {
            $file->move($this->uploadsDirectory, $newFilename);
        } catch (FileException $e) {
            throw new FileException('Error al subir el archivo: ' . $e->getMessage());
        }

        return $newFilename;
    }

    /**
     * Redimensionar una imagen a las dimensiones dadas.
     *
     * @param string $filename
     * @param int $width
     * @param int|null $height
     * @return void
     */
    public function resizeImage(string $filename, int $width, ?int $height = null): void
    {
        $filepath = $this->uploadsDirectory . '/' . $filename;
        $image = $this->createImageResource($filepath);

        if (!$image) {
            throw new \Exception('Formato de imagen no soportado.');
        }

        // Calcular el alto si no se proporciona (mantener la proporción)
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        if (!$height) {
            $height = (int) (($width / $originalWidth) * $originalHeight);
        }

        $newImage = imagecreatetruecolor($width, $height);
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);

        // Guardar la imagen redimensionada
        $this->saveImageResource($newImage, $filepath);
    }

    /**
     * Eliminar una imagen del directorio.
     *
     * @param string $filename
     * @return bool
     */
    public function deleteImage(string $filename): bool
    {
        $filepath = $this->uploadsDirectory . '/' . $filename;

        if (file_exists($filepath)) {
            return unlink($filepath);
        }

        return false;
    }

    /**
     * Crear un recurso de imagen desde un archivo.
     *
     * @param string $filepath
     * @return resource|false
     */
    private function createImageResource(string $filepath)
    {
        $mimeType = mime_content_type($filepath);

        switch ($mimeType) {
            case 'image/jpeg':
                return imagecreatefromjpeg($filepath);
            case 'image/png':
                return imagecreatefrompng($filepath);
            case 'image/gif':
                return imagecreatefromgif($filepath);
            default:
                return false;
        }
    }

    /**
     * Guardar el recurso de imagen en su formato original.
     *
     * @param resource $image
     * @param string $filepath
     * @return void
     */
    private function saveImageResource($image, string $filepath): void
    {
        $mimeType = mime_content_type($filepath);

        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($image, $filepath);
                break;
            case 'image/png':
                imagepng($image, $filepath);
                break;
            case 'image/gif':
                imagegif($image, $filepath);
                break;
            default:
                throw new \Exception('Formato de imagen no soportado para guardar.');
        }

        imagedestroy($image);
    }
}
