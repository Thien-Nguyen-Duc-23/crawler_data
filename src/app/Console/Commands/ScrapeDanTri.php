<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Article;
use Weidner\Goutte\GoutteFacade;

class ScrapeDanTri extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:dantri';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $url = 'https://dantri.com.vn/lao-dong-viec-lam.htm';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $crawler = GoutteFacade::request('GET', $this->url);
        $linkPost = $crawler->filter('h3.article-title a')->each(function ($node) {
            return $node->attr("href");
        });

        foreach ($linkPost as $link) {
            self::scrapedata($link);
        }
    }

    public static function scrapedata($url)
    {
        $crawler = GoutteFacade::request('GET', $url);

        $title = $crawler->filter('article.singular-container h1')->each(function ($node) {
            return $node->text();
        })[0] ?? null;

        $description = $crawler->filter('article.singular-container h2.singular-sapo')->each(function ($node) {
            return $node->text();
        })[0] ?? null;
        $description = str_replace('Dân trí', '', $description);


        $content = $crawler->filter('article.singular-container div.singular-content')->each(function ($node) {
            return $node->text();
        })[0] ?? null;

        if ($title && $content && $description) {
            $dataPost = [
                'title' => $title,
                'content' => $content,
                'description' => $description
            ];

            Article::create($dataPost);
        }
    }
}
