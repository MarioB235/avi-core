<?php

namespace Tests\Support;

use Symfony\Component\Process\Process;

trait RunsMovimientoConcurrentWorkers
{
    /**
     * @param  list<array<string, mixed>>  $jobs
     * @return list<array<string, mixed>>
     */
    protected function runMovimientoWorkersInParallel(array $jobs): array
    {
        $tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'avicore-mov-conc-'.uniqid('', true);
        mkdir($tempDir);

        $processes = [];
        $resultPaths = [];

        foreach ($jobs as $index => $job) {
            $payloadPath = $tempDir.DIRECTORY_SEPARATOR.'job_'.$index.'.json';
            $resultPath = $tempDir.DIRECTORY_SEPARATOR.'result_'.$index.'.json';
            $job['result_path'] = $resultPath;
            file_put_contents($payloadPath, json_encode($job, JSON_THROW_ON_ERROR));
            $resultPaths[] = $resultPath;

            $process = new Process([
                PHP_BINARY,
                base_path('tests/Support/movimiento_concurrencia_worker.php'),
                $payloadPath,
            ], base_path());
            $process->start();
            $processes[] = $process;
        }

        foreach ($processes as $process) {
            $process->wait();
            if (! $process->isSuccessful()) {
                $this->fail('Worker falló: '.$process->getErrorOutput().$process->getOutput());
            }
        }

        $results = [];
        foreach ($resultPaths as $path) {
            $results[] = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        }

        return $results;
    }
}
