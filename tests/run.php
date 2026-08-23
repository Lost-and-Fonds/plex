<?php

declare(strict_types=1);

use PlexNative\PlexBroadcast;
use Stashd\PluginSdk as Sdk;

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/src/PlexBroadcast.php';

function plexAssert(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

final class PlexFixtureStaging implements Sdk\StagingArea
{
    public function write(string $relativePath, string $content, ?string $mediaType = null): Sdk\StagedArtifact
    {
        return new Sdk\StagedArtifact('staged:' . $relativePath, $mediaType ?? 'application/octet-stream', strlen($content));
    }

    public function stage(string $relativePath, ?string $mediaType = null): Sdk\StagedArtifact
    {
        return new Sdk\StagedArtifact('staged:' . $relativePath, $mediaType ?? 'application/octet-stream');
    }
}

final class PlexFixtureHttp implements Sdk\HttpClient
{
    /** @var list<array{method: string, url: string, credential: ?string}> */
    public array $requests = [];

    public function request(string $method, string $url, array $headers = [], ?string $body = null, ?string $credential = null): Sdk\HttpResponse
    {
        plexAssert($credential === 'plex-api-token', 'credential grant was not requested');
        $this->requests[] = compact('method', 'url', 'credential');

        return match ($url) {
            'https://plex.test/identity' => new Sdk\HttpResponse(200, [], '<MediaContainer/>'),
            'https://plex.test/library/sections' => new Sdk\HttpResponse(200, [], '<MediaContainer><Directory key="3" title="TV"/></MediaContainer>'),
            'https://plex.test/library/sections/3/refresh' => new Sdk\HttpResponse(200),
            default => new Sdk\HttpResponse(404, [], 'not found'),
        };
    }
}

$plugin = new PlexBroadcast();
$settings = [
    new Sdk\Setting('title', Sdk\OptionValue::text('Fixture Library')),
    new Sdk\Setting('captions', Sdk\OptionValue::text('creator_only')),
    new Sdk\Setting('caption_languages', Sdk\OptionValue::text('en')),
    new Sdk\Setting('server_url', Sdk\OptionValue::text('https://plex.test')),
    new Sdk\Setting('library_id', Sdk\OptionValue::text('3')),
    new Sdk\Setting('credential_name', Sdk\OptionValue::text('plex-api-token')),
];
$source = new Sdk\Source('source-1', [new Sdk\Setting('season', Sdk\OptionValue::number(3))]);
$request = new Sdk\PublishRequest('broadcast-1', $settings, [$source], [
    new Sdk\Item('item-1', 'A/Title', [
        new Sdk\ItemResource('asset-1', 'video', mediaType: 'video/webm'),
        new Sdk\ItemResource('caption-1', 'subtitle'),
    ], 'source-1'),
], new PlexFixtureStaging());
$publication = $plugin->publish($request);
plexAssert(($publication->files[0]->relativePath ?? null) === 'Season 03/S03E001 - A_Title.webm', 'video layout changed');
plexAssert(($publication->files[1]->relativePath ?? null) === 'Season 03/S03E001 - A_Title.en.vtt', 'caption layout changed');

$http = new PlexFixtureHttp();
$context = new Sdk\PluginContext(http: $http);
$operation = $plugin->operation(new Sdk\OperationRequest('discover-libraries', $settings), $context);
plexAssert(($operation->choices[0]->value ?? null) === '3', 'library discovery failed');
$plugin->finalize(new Sdk\FinalizationRequest($request, $publication), $context);
plexAssert(count($http->requests) === 2, 'expected discovery and refresh requests');

echo "Plex provider contract: PASS\n";
