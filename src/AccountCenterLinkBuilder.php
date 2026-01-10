<?php

namespace Robo\AccountLinks;

final class AccountCenterLinkBuilder
{
    public function __construct(
        private readonly string $baseUrl = 'https://account.robo.dev',
        private readonly string $anchorBaseUrl = 'https://account.robo.dev/my-robo-meister-account'
    ) {
    }

    /**
     * @param array<string, string|null> $query
     */
    public function buildSectionLink(string $orgId, string $section, array $query = []): string
    {
        $path = match ($section) {
            'billing' => '/orgs/%s/billing',
            'team' => '/orgs/%s/team',
            'apps' => '/orgs/%s/apps',
            'security' => '/orgs/%s/security',
            default => '/orgs/%s/overview',
        };

        $url = rtrim($this->baseUrl, '/') . sprintf($path, rawurlencode($orgId));

        return $this->appendQuery($url, $query);
    }

    /**
     * @param array<string, string|null> $query
     */
    public function buildAnchorLink(string $anchor, array $query = []): string
    {
        $anchorValue = match ($anchor) {
            'applications' => '#applications',
            'integrations' => '#integrations',
            'security' => '#security',
            'billing' => '#billing',
            'notifications' => '#notifications',
            'profile' => '#profile',
            default => '#overview',
        };

        $url = rtrim($this->anchorBaseUrl, '/') . $anchorValue;

        return $this->appendQuery($url, $query);
    }

    /**
     * @param array<string, string|null> $query
     */
    private function appendQuery(string $url, array $query): string
    {
        $query = array_filter($query, static fn ($value) => $value !== null && $value !== '');

        if ($query === []) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }
}
