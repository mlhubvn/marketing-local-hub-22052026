<?php

namespace Modules\AppCustomDomain\Support;

use Illuminate\Support\Facades\Http;

class DomainHealthChecker
{
    public function inspect(string $domain, string $verificationToken = ''): array
    {
        $domain = strtolower(trim($domain));
        $txtVerified = $verificationToken !== '' && $this->hasTxt($domain, $verificationToken);
        $cnameTargets = $this->cnameTargets($domain);

        return [
            'txt_verified' => $txtVerified,
            'cname_targets' => $cnameTargets,
            'has_cname' => $cnameTargets !== [],
            'ssl_ready' => $this->sslReady($domain),
        ];
    }

    public function hasTxt(string $domain, string $token): bool
    {
        $token = $this->normalizeTxtValue($token);

        foreach ($this->nativeTxtValues($domain) as $txt) {
            if (hash_equals($token, $this->normalizeTxtValue($txt))) {
                return true;
            }
        }

        foreach ($this->dohTxtValues($domain) as $txt) {
            if (hash_equals($token, $this->normalizeTxtValue($txt))) {
                return true;
            }
        }

        return false;
    }

    protected function nativeTxtValues(string $domain): array
    {
        $records = @dns_get_record($domain, DNS_TXT);

        if ($records === false || $records === []) {
            return [];
        }

        return collect($records)
            ->map(function (array $record): string {
                if (isset($record['entries']) && is_array($record['entries'])) {
                    return implode('', $record['entries']);
                }

                return (string) ($record['txt'] ?? '');
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function dohTxtValues(string $domain): array
    {
        $values = [];

        foreach ($this->dohAnswers($domain, 'TXT') as $answer) {
            if (($answer['type'] ?? null) !== 16 || empty($answer['data'])) {
                continue;
            }

            $values[] = (string) $answer['data'];
        }

        return $values;
    }

    protected function dohAnswers(string $domain, string $type): array
    {
        $providers = [
            'https://cloudflare-dns.com/dns-query',
            'https://dns.google/resolve',
        ];

        $answers = [];

        foreach ($providers as $provider) {
            try {
                $response = Http::acceptJson()
                    ->timeout(5)
                    ->get($provider, ['name' => $domain, 'type' => $type]);
            } catch (\Throwable) {
                continue;
            }

            if (! $response->ok()) {
                continue;
            }

            $answers = array_merge($answers, (array) data_get($response->json(), 'Answer', []));
        }

        return $answers;
    }

    protected function normalizeTxtValue(string $value): string
    {
        $value = trim($value);

        if (str_contains($value, '"') && preg_match_all('/"((?:\\\\.|[^"\\\\])*)"/', $value, $matches)) {
            return implode('', array_map('stripcslashes', $matches[1]));
        }

        return trim($value, '"');
    }

    protected function cnameTargets(string $domain): array
    {
        $records = @dns_get_record($domain, DNS_CNAME);

        $targets = $records === false ? [] : collect($records)
            ->map(fn (array $record): string => trim((string) ($record['target'] ?? ''), '.'))
            ->filter()
            ->values()
            ->all();

        $targets = array_merge($targets, $this->dohCnameTargets($domain));

        if ($targets !== []) {
            return array_values(array_unique($targets));
        }

        return $this->hasAddressRecords($domain) ? [__('A/AAAA records found')] : [];
    }

    protected function dohCnameTargets(string $domain): array
    {
        $targets = [];

        foreach ($this->dohAnswers($domain, 'CNAME') as $answer) {
            if (($answer['type'] ?? null) !== 5 || empty($answer['data'])) {
                continue;
            }

            $targets[] = trim((string) $answer['data'], '.');
        }

        return $targets;
    }

    protected function hasAddressRecords(string $domain): bool
    {
        $records = array_merge(
            $this->nativeAddressRecords($domain, DNS_A),
            $this->nativeAddressRecords($domain, DNS_AAAA)
        );

        if ($records !== []) {
            return true;
        }

        foreach (['A', 'AAAA'] as $type) {
            foreach ($this->dohAnswers($domain, $type) as $answer) {
                if (in_array(($answer['type'] ?? null), [1, 28], true) && ! empty($answer['data'])) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function nativeAddressRecords(string $domain, int $type): array
    {
        $records = @dns_get_record($domain, $type);

        if ($records === false) {
            return [];
        }

        return collect($records)
            ->filter(fn (array $record): bool => ! empty($record['ip']) || ! empty($record['ipv6']))
            ->filter()
            ->values()
            ->all();
    }

    protected function sslReady(string $domain): bool
    {
        $context = stream_context_create(['ssl' => ['capture_peer_cert' => true, 'verify_peer' => false, 'verify_peer_name' => false]]);
        $client = @stream_socket_client('ssl://'.$domain.':443', $errno, $errstr, 4, STREAM_CLIENT_CONNECT, $context);

        if (! is_resource($client)) {
            return false;
        }

        fclose($client);

        return true;
    }
}
