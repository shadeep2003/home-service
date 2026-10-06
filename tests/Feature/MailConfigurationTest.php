<?php
namespace Tests\Feature;
use Symfony\Component\Process\Process;
use Tests\TestCase;
class MailConfigurationTest extends TestCase
{
    public function test_smtp_scheme_accepts_legacy_encryption_labels_and_standard_schemes(): void
    {
        foreach (['tls' => 'smtp', 'ssl' => 'smtps', 'smtp' => 'smtp', 'smtps' => 'smtps'] as $input => $expected) {
            $process = new Process([PHP_BINARY, '-r', 'require "vendor/autoload.php"; $config = require "config/mail.php"; echo $config["mailers"]["smtp"]["scheme"];'], base_path(), ['MAIL_SCHEME' => $input]);
            $process->run();
            $this->assertTrue($process->isSuccessful());
            $this->assertSame($expected, $process->getOutput());
        }
    }
    public function test_smtp_scheme_defaults_to_implicit_tls_on_port_465(): void
    {
        $process = new Process([PHP_BINARY, '-r', 'require "vendor/autoload.php"; $config = require "config/mail.php"; echo $config["mailers"]["smtp"]["scheme"];'], base_path(), ['MAIL_SCHEME' => false, 'MAIL_PORT' => '465']);
        $process->run();
        $this->assertTrue($process->isSuccessful());
        $this->assertSame('smtps', $process->getOutput());
    }
}
