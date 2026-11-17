<?php
declare(strict_types=1);

final class UploadConcurrencyGuard
{
    /** @var resource|null */
    private $handle = null;

    public function acquire(string $idUsuario): bool
    {
        $directorio = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'dlgc_rrhh_upload_locks';
        if (!is_dir($directorio) && !mkdir($directorio, 0700, true) && !is_dir($directorio)) {
            throw new RuntimeException('No se pudo preparar el control de concurrencia.');
        }

        $nombre = hash('sha256', $idUsuario) . '.lock';
        $handle = fopen($directorio . DIRECTORY_SEPARATOR . $nombre, 'c+b');
        if ($handle === false) {
            throw new RuntimeException('No se pudo preparar el control de concurrencia.');
        }

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            return false;
        }

        $this->handle = $handle;
        return true;
    }

    public function __destruct()
    {
        if (is_resource($this->handle)) {
            flock($this->handle, LOCK_UN);
            fclose($this->handle);
        }
    }
}
