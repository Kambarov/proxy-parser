<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ParseProxyList extends Command
{
    private const SSL_SUPPORT_LETTER = 'S';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proxy:parse {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse the list of vpns by the given url';

    /**
     * Execute the console command.
     *
     * @return int|void
     * @throws \JsonException
     */
    public function handle()
    {
        if (!filter_var($this->argument('url'), FILTER_VALIDATE_URL)) {
            return $this->error('the url should be valid url');
        }

        $response = Http::get($this->argument('url'));

        $explodedResult = explode('\n', json_encode($response->body(), JSON_THROW_ON_ERROR));

        $filteredResult = array_filter($explodedResult, function ($value) {
            return preg_match('/^\d{1,3}./', $value)
                && strpos($value, self::SSL_SUPPORT_LETTER);
        });

        $bar = $this->output->createProgressBar(count($filteredResult));
        $bar->start();

        $vpns  = array_map(function ($value) {
            if (preg_match('/^(\d[\d.]+):(\d+)\b/', $value, $matches)) {
                $vpn['ip'] = $matches[1];
                $vpn['port'] = $matches[2];

                return $vpn;
            }
        }, $filteredResult);

        foreach ($vpns as $vpn) {

            $exists = (bool) DB::table('proxy_lists')
                ->where('ip', $vpn['ip'])
                ->first();

            if (!$exists) {
                DB::table('proxy_lists')
                    ->insert([
                        'ip' => $vpn['ip'],
                        'port' => $vpn['port'],
                        'url' => $this->argument('url')
                    ]);
            }

            $bar->advance();
        }

        $bar->finish();

        $activeIps = DB::table('proxy_lists')
            ->where('blocked', false)
            ->count();

        $this->info('', '\n');

        $this->info('Task accomplished successfully. We have ' . $activeIps . ' in the database');

        return 0;
    }
}
