<?php

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class Proxy
{
    private const ERROR_MESSAGE = 'Try running proxy:parse {url} first';

    /**
     * @param Http $http
     * @param string $url
     * @param array $body
     * @return string
     */
    public function makeRequest(Http $http, string $url, array $body): string
    {
        $vpn = self::query()
            ->first();

        if (!is_null($vpn)) {
            $response = $http::withHeaders([
                'proxy' => "tcp://$vpn->ip:$vpn->port"
            ])->post($url, $body);

            do {
                $vpn->update([
                    'blocked' => true
                ]);

                $vpn = self::query()
                    ->first();
            } while ($response->failed());

            return $response->body();
        }

        return self::ERROR_MESSAGE;
    }

    /**
     * @return Builder
     */
    private static function query(): Builder
    {
        return DB::table('proxy_lists')
            ->where('blocked', false)
            ->inRandomOrder();
    }
}
