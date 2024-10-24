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
     * TODO: TRY THIS CODE: $timeout = Platform::getEnv('...');
     * @param array<string> $params
     * @param callable(Process): void $finish
     */
    public function run(array $command, callable $callback = null): StreamedResponse
    {
        $timeout = $_ENV['SHOPWARE_INSTALLER_TIMEOUT'] ?? 900;
        if (!is_numeric($timeout)) {
            $timeout = 900;
        }

        $process = new Process($command);
        $process->setTimeout((int) $timeout);

        if ($callback) {
            $callback($process);
        }

        $process->run();

        return new StreamedResponse(function () use ($process) {
            echo $process->getOutput();
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
