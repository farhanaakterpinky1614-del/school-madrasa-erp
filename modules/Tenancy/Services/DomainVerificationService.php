<?php

namespace Modules\Tenancy\Services;

use Modules\Tenancy\Models\TenantDomain;

/**
 * Custom-domain DNS TXT / HTTP token verification (Phase 16).
 *
 * Expected TXT: host `_sme-verify.{domain}` with value `sme-verify={token}`
 * Or HTTP GET `{scheme}://{host}/.well-known/sme-domain-verify.txt` containing the token.
 */
class DomainVerificationService
{
    public function txtName(TenantDomain $domain): string
    {
        return '_sme-verify.'.$domain->host;
    }

    public function expectedTxtValue(TenantDomain $domain): string
    {
        return 'sme-verify='.($domain->verification_token ?? '');
    }

    public function httpChallengePath(): string
    {
        return '/.well-known/sme-domain-verify.txt';
    }

    /**
     * @return array{verified: bool, method: ?string, detail: string}
     */
    public function verify(TenantDomain $domain): array
    {
        if ($domain->type !== TenantDomain::TYPE_CUSTOM) {
            return ['verified' => false, 'method' => null, 'detail' => 'Only custom domains require verification.'];
        }

        if (! $domain->verification_token) {
            $domain->beginVerification();
            $domain->refresh();
        }

        $driver = (string) config('sme.domain_verification.driver', 'dns');

        if ($driver === 'always_pass') {
            $domain->markVerified();

            return ['verified' => true, 'method' => 'always_pass', 'detail' => 'Verified via always_pass driver (tests/local).'];
        }

        $txt = $this->checkDnsTxt($domain);
        if ($txt['ok']) {
            $domain->markVerified();

            return ['verified' => true, 'method' => 'dns_txt', 'detail' => $txt['detail']];
        }

        $http = $this->checkHttpChallenge($domain);
        if ($http['ok']) {
            $domain->markVerified();

            return ['verified' => true, 'method' => 'http', 'detail' => $http['detail']];
        }

        $domain->forceFill([
            'verification_status' => TenantDomain::STATUS_FAILED,
        ])->save();

        return [
            'verified' => false,
            'method' => null,
            'detail' => trim($txt['detail'].' '.$http['detail']),
        ];
    }

    /**
     * @return array{ok: bool, detail: string}
     */
    protected function checkDnsTxt(TenantDomain $domain): array
    {
        $name = $this->txtName($domain);
        $expected = $this->expectedTxtValue($domain);

        $records = $this->lookupTxt($name);

        foreach ($records as $value) {
            $normalized = trim(str_replace('"', '', $value));
            if (strcasecmp($normalized, $expected) === 0 || str_contains($normalized, $domain->verification_token ?? '')) {
                return ['ok' => true, 'detail' => "TXT match on {$name}."];
            }
        }

        return ['ok' => false, 'detail' => "No matching TXT on {$name}."];
    }

    /**
     * @return list<string>
     */
    protected function lookupTxt(string $name): array
    {
        $override = config('sme.domain_verification.txt_overrides', []);
        if (is_array($override) && array_key_exists($name, $override)) {
            $val = $override[$name];

            return is_array($val) ? array_map('strval', $val) : [(string) $val];
        }

        if (! function_exists('dns_get_record')) {
            return [];
        }

        try {
            $records = @dns_get_record($name, DNS_TXT) ?: [];
        } catch (\Throwable) {
            return [];
        }

        $out = [];
        foreach ($records as $row) {
            if (isset($row['txt'])) {
                $out[] = (string) $row['txt'];
            }
        }

        return $out;
    }

    /**
     * @return array{ok: bool, detail: string}
     */
    protected function checkHttpChallenge(TenantDomain $domain): array
    {
        $url = 'https://'.$domain->host.$this->httpChallengePath();
        $token = (string) $domain->verification_token;

        $override = config('sme.domain_verification.http_overrides', []);
        if (is_array($override) && array_key_exists($domain->host, $override)) {
            $body = (string) $override[$domain->host];
            if (trim($body) === $token) {
                return ['ok' => true, 'detail' => "HTTP challenge matched for {$domain->host}."];
            }

            return ['ok' => false, 'detail' => "HTTP override present but token mismatch for {$domain->host}."];
        }

        if (! (bool) config('sme.domain_verification.allow_live_http', false)) {
            return ['ok' => false, 'detail' => 'Live HTTP challenge disabled.'];
        }

        try {
            $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
            $body = @file_get_contents($url, false, $ctx);
            if ($body !== false && trim($body) === $token) {
                return ['ok' => true, 'detail' => "HTTP challenge matched at {$url}."];
            }
        } catch (\Throwable) {
            // fall through
        }

        return ['ok' => false, 'detail' => "HTTP challenge failed at {$url}."];
    }
}
