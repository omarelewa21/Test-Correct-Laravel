<?php namespace tcCore\Services;


use Illuminate\Support\Str;

class QuestionHtmlConverter
{
    private $dom;
    private $originalHtml;



    public function __construct($html)
    {
        $this->originalHtml = $html;
        $cleanedHtml = $this->filterMathMLTags($html);

        $this->dom = new \DOMDocument();
        if ($cleanedHtml) {
            @$this->dom->loadHTML($cleanedHtml);
        }
    }

    public function convertImageSourcesWithPatternToNamedRoute($routeName, $pattern)
    {
        $searchAndReplace = $this->getSearchAndReplace($pattern, $routeName);

        if ($searchAndReplace) {
            foreach ($searchAndReplace as $search => $replace) {
                $this->originalHtml = str_replace($search, $replace, $this->originalHtml);
            }
        }

        return $this->originalHtml;
    }

    private function filterMathMLTags($html)
    {
        return preg_replace('/<math(.*)<\/math>/i', '', $html);
    }

    /**
     * @param $pattern
     * @param $routeName
     * @return array
     */
    private function getSearchAndReplace($pattern, $routeName): array
    {
        $searchAndReplace = [];
        $imageTags = $this->dom->getElementsByTagName('img');

        foreach ($imageTags as $img) {
            $imageSource = $img->getAttribute('src');

            if (Str::contains($imageSource, $pattern)) {
                $imageName = explode($pattern, $imageSource)[1];

                $searchAndReplace[$imageSource] = route($routeName, $imageName);
            }
        }

        return $searchAndReplace;
    }

}
