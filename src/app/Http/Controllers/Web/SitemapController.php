<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Category;
use App\Services\SettingService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __construct(
        protected SettingService $settingService
    ) {}

    public function index(): Response
    {
        $xml = Cache::remember('sitemap', 3600, function () {
            $materials = Material::where('state', 'published')
                ->select('slug', 'updated_at')
                ->get();

            $categories = Category::select('slug', 'updated_at')
                ->get();

            $settings = $this->settingService->getAllSettings();
            $siteUrl = $settings['site_url'] ?? url('/');

            return $this->generateXml($siteUrl, $materials, $categories);
        });

        return response($xml, 200)
            ->header('Content-Type', 'text/xml');
    }

    private function generateXml(string $siteUrl, $materials, $categories): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $xml .= "    <url>\n";
        $xml .= "        <loc>{$siteUrl}</loc>\n";
        $xml .= "        <priority>1.0</priority>\n";
        $xml .= "    </url>\n";

        foreach ($categories as $category) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>{$siteUrl}/category/{$category->slug}</loc>\n";
            $xml .= "        <lastmod>{$category->updated_at->toW3cString()}</lastmod>\n";
            $xml .= "        <priority>0.8</priority>\n";
            $xml .= "    </url>\n";
        }

        foreach ($materials as $material) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>{$siteUrl}/material/{$material->slug}</loc>\n";
            $xml .= "        <lastmod>{$material->updated_at->toW3cString()}</lastmod>\n";
            $xml .= "        <priority>0.6</priority>\n";
            $xml .= "    </url>\n";
        }

        $xml .= '</urlset>';
        return $xml;
    }
}
