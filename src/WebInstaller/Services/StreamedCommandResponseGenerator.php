<?php
declare(strict_types=1);

namespace Shopware\WebInstaller\Services;

use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
#[Package('core')]
class StreamedCommandResponseGenerator
{
    private const DEFAULT_TIMEOUT = 900;

    /**
     * @param array<string> $params
     * @param callable(Process): void $finish
     */
    public function run(array $params, callable $finish): StreamedResponse
    {
        $process = new Process($params);
        $process->setEnv(['COMPOSER_HOME' => sys_get_temp_dir() . '/composer']);

        // Read timeout from environment variable or use default value
        $timeout = (int) ($_ENV['SHOPWARE_INSTALLER_TIMEOUT'] ?? self::DEFAULT_TIMEOUT);
        $process->setTimeout($timeout);

        $process->start();

        return new StreamedResponse(function () use ($process, $finish): void {
            foreach ($process->getIterator() as $item) {
                \assert(\is_string($item));
                echo $item;
                flush();
            }

            $finish($process);
        });
    }

    /**
     * @param array<string> $params
     */
    public function runJSON(array $params, ?callable $finish = null): StreamedResponse
    {
        return $this->run($params, function (Process $process) use ($finish): void {
            if ($finish !== null) {
                $finish($process);
            }

            echo json_encode([
                'success' => $process->isSuccessful(),
            ]);
        });
    }
}
