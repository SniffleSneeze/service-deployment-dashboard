<?php
declare(strict_types=1);

namespace App\Model;

use Symfony\Component\Serializer\Annotation\SerializedName;

class HealthCheckResponseModel
{
    #[SerializedName('app')]
    private bool $app;

    #[SerializedName('version')]
    private string $version;

    #[SerializedName('lastCommitDate')]
    private string $lastCommitDate;

    #[SerializedName('lastBuildStartTime')]
    private string $lastBuildStartTime;

    #[SerializedName('database')]
    private bool $database;

    public function __construct(
        bool $app = false,
        string $version = '',
        string $lastCommitDate = '',
        string $lastBuildStartTime = '',
        bool $database = false
    ) {
        $this->app = $app;
        $this->version = $version;
        $this->lastCommitDate = $lastCommitDate;
        $this->lastBuildStartTime = $lastBuildStartTime;
        $this->database = $database;
    }

    public function isApp(): bool
    {
        return $this->app;
    }

    public function setApp(bool $app): self
    {
        $this->app = $app;
        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    public function getLastCommitDate(): string
    {
        return $this->lastCommitDate;
    }

    public function setLastCommitDate(string $lastCommitDate): self
    {
        $this->lastCommitDate = $lastCommitDate;
        return $this;
    }

    public function getLastBuildStartTime(): string
    {
        return $this->lastBuildStartTime;
    }

    public function setLastBuildStartTime(string $lastBuildStartTime): self
    {
        $this->lastBuildStartTime = $lastBuildStartTime;
        return $this;
    }

    public function isDatabase(): bool
    {
        return $this->database;
    }

    public function setDatabase(bool $database): self
    {
        $this->database = $database;
        return $this;
    }

}
