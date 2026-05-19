<?php

namespace App\Controllers;

class LegalController extends BaseController
{
    private function siteConfig(): array
    {
        return ['site' => config('Site')];
    }

    public function cgu(): string
    {
        return view('Legal/cgu', ['title' => 'CGU'] + $this->siteConfig());
    }

    public function confidentialite(): string
    {
        return view('Legal/confidentialite', ['title' => 'Confidentialité'] + $this->siteConfig());
    }

    public function mentions(): string
    {
        return view('Legal/mentions', ['title' => 'Mentions légales'] + $this->siteConfig());
    }
}
