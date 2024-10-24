<?php
declare(strict_types=1);

namespace Shopware\WebInstaller\Tests\Services;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\WebInstaller\Services\StreamedCommandResponseGenerator;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
#[CoversClass(StreamedCommandResponseGenerator::class)]
class StreamedCommandResponseGeneratorTest extends TestCase
{
    private StreamedCommandResponseGenerator $streamedCommandResponseGenerator;

    protected function setUp(): void
    {
        $this->streamedCommandResponseGenerator = new StreamedCommandResponseGenerator();
        // Ensure environment variable is not set at start of each test
        unset($_ENV['SHOPWARE_INSTALLER_TIMEOUT']);
    }

    public function testRun(): void
    {
        $response = $this->streamedCommandResponseGenerator->run(['echo', 'foo'], function (Process $process): void {
            static::assertTrue($process->isSuccessful());
            static::assertEquals(900, $process->getTimeout());
        });

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        static::assertSame('foo', trim((string) $content));
    }

    public function testRunJSON(): void
    {
        $response = $this->streamedCommandResponseGenerator->runJSON(['echo', 'foo']);

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        static::assertSame('foo' . \PHP_EOL . '{"success":true}', $content);
    }

    public function testRunWithCustomTimeout(): void
    {
        $_ENV['SHOPWARE_INSTALLER_TIMEOUT'] = '1800';

        $process = new Process(['echo', 'foo']);
        $response = $this->streamedCommandResponseGenerator->run(['echo', 'foo'], function (Process $process): void {
            static::assertEquals(1800, $process->getTimeout());
        });

        ob_start();
        $response->sendContent();
        ob_get_clean();
    }

    public function testRunWithInvalidTimeout(): void
    {
        $_ENV['SHOPWARE_INSTALLER_TIMEOUT'] = 'invalid';

        $process = new Process(['echo', 'foo']);
        $response = $this->streamedCommandResponseGenerator->run(['echo', 'foo'], function (Process $process): void {
            static::assertEquals(900, $process->getTimeout());
        });

        ob_start();
        $response->sendContent();
        ob_get_clean();
    }

    protected function tearDown(): void
    {
        // Cleanup environment variable
        unset($_ENV['SHOPWARE_INSTALLER_TIMEOUT']);
    }
}
