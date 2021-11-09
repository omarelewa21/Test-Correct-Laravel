<?php namespace tcCore\Services;


use Illuminate\Support\Str;

class QuestionHtmlConverter
{
    private $dom;



    public function __construct($html)
    {
        $this->dom = new \DOMDocument();
        $this->dom->loadHTML($html);
    }

    public function convertImageSourcesWithPatternToNamedRoute($routeName, $pattern)
    {
        $imageTags = $this->dom->getElementsByTagName('img');

        foreach ($imageTags as $img) {
            $imageSource = $img->getAttribute('src');

            if (Str::contains($imageSource, $pattern)) {
                $imageName = explode($pattern, $imageSource)[1];

                $img->setAttribute('src', route($routeName, $imageName));
            }
        }

        return $this->dom->saveHTML();
    }

}
