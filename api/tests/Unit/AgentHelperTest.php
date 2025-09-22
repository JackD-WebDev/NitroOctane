<?php

use App\Http\Helpers\AgentHelper;

it('returns unknown for null user agent', function () {
    $result = AgentHelper::parseUserAgent(null);
    
    expect($result)->toBe([
        'browser' => 'Unknown',
        'platform' => 'Unknown',
        'original' => null,
    ]);
});

it('returns unknown for empty user agent', function () {
    $result = AgentHelper::parseUserAgent('');
    
    expect($result)->toBe([
        'browser' => 'Unknown',
        'platform' => 'Unknown',
        'original' => '',
    ]);
});

it('parses Chrome on Windows', function () {
    $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
    $result = AgentHelper::parseUserAgent($userAgent);
    
    expect($result['browser'])->toBe('Chrome');
    expect($result['platform'])->toBe('Windows');
    expect($result['original'])->toBe($userAgent);
});

it('parses Firefox on Windows', function () {
    $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0';
    $result = AgentHelper::parseUserAgent($userAgent);
    
    expect($result['browser'])->toBe('Firefox');
    expect($result['platform'])->toBe('Windows');
    expect($result['original'])->toBe($userAgent);
});

it('parses Safari on Mac', function () {
    $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15';
    $result = AgentHelper::parseUserAgent($userAgent);
    
    expect($result['browser'])->toBe('Safari');
    expect($result['platform'])->toBe('Mac');
    expect($result['original'])->toBe($userAgent);
});

it('parses Chrome on Mac', function () {
    $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
    $result = AgentHelper::parseUserAgent($userAgent);
    
    expect($result['browser'])->toBe('Chrome');
    expect($result['platform'])->toBe('Mac');
    expect($result['original'])->toBe($userAgent);
});

it('parses Chrome on Linux', function () {
    $userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
    $result = AgentHelper::parseUserAgent($userAgent);
    
    expect($result['browser'])->toBe('Chrome');
    expect($result['platform'])->toBe('Linux');
    expect($result['original'])->toBe($userAgent);
});

it('parses Chrome on Android', function () {
    $userAgent = 'Mozilla/5.0 (Linux; Android 11; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.120 Mobile Safari/537.36';
    $result = AgentHelper::parseUserAgent($userAgent);
    
    expect($result['browser'])->toBe('Chrome');
    expect($result['platform'])->toBe('Android');
    expect($result['original'])->toBe($userAgent);
});

it('parses Safari on iPhone', function () {
    $userAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1';
    $result = AgentHelper::parseUserAgent($userAgent);
    
    expect($result['browser'])->toBe('Safari');
    expect($result['platform'])->toBe('iOS');
    expect($result['original'])->toBe($userAgent);
});

it('parses Safari on iPad', function () {
    $userAgent = 'Mozilla/5.0 (iPad; CPU OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1';
    $result = AgentHelper::parseUserAgent($userAgent);
    
    expect($result['browser'])->toBe('Safari');
    expect($result['platform'])->toBe('iOS');
    expect($result['original'])->toBe($userAgent);
});

it('parses Edge on Windows', function () {
    $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36 Edg/91.0.864.59';
    $result = AgentHelper::parseUserAgent($userAgent);
    
    expect($result['browser'])->toBe('Edge');
    expect($result['platform'])->toBe('Windows');
    expect($result['original'])->toBe($userAgent);
});

it('parses Internet Explorer on Windows', function () {
    $userAgent = 'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko';
    $result = AgentHelper::parseUserAgent($userAgent);
    
    expect($result['browser'])->toBe('Internet Explorer');
    expect($result['platform'])->toBe('Windows');
    expect($result['original'])->toBe($userAgent);
});

it('parses old IE with MSIE in user agent', function () {
    $userAgent = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0)';
    $result = AgentHelper::parseUserAgent($userAgent);
    
    expect($result['browser'])->toBe('Internet Explorer');
    expect($result['platform'])->toBe('Windows');
    expect($result['original'])->toBe($userAgent);
});

it('handles unknown browser and platform', function () {
    $userAgent = 'SomeCustomBrowser/1.0 (CustomOS)';
    $result = AgentHelper::parseUserAgent($userAgent);
    
    expect($result['browser'])->toBe('Unknown');
    expect($result['platform'])->toBe('Unknown');
    expect($result['original'])->toBe($userAgent);
});

it('prioritizes Chrome over Safari when both present', function () {
    $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
    $result = AgentHelper::parseUserAgent($userAgent);
    
    expect($result['browser'])->toBe('Chrome');
    expect($result['platform'])->toBe('Windows');
});

it('prioritizes specific platforms over generic ones', function () {
    $userAgent = 'Mozilla/5.0 (Linux; Android 11; SM-G991B) AppleWebKit/537.36';
    $result = AgentHelper::parseUserAgent($userAgent);
    
    expect($result['platform'])->toBe('Android');
});