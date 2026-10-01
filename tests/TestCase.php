<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tests must never reach a real AI server (OpenAI or a local Ollama):
        // any HTTP call not explicitly faked by a test throws instead.
        Http::preventStrayRequests();
    }
}
