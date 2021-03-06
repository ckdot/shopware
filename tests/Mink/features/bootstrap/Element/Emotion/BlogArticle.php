<?php

namespace Shopware\Tests\Mink\Element\Emotion;

use Behat\Mink\Element\NodeElement;
use Shopware\Tests\Mink\Element\MultipleElement;
use Shopware\Tests\Mink\Helper;

/**
 * Element: BlogArticle
 * Location: Emotion element for blog articles
 *
 * Available retrievable properties (per blog article):
 * - image (string, e.g. "beach1503f8532d4648.jpg")
 * - link (string, e.g. "/Campaign/index/emotionId/6")
 * - alt (string, e.g. "foo")
 * - title (string, e.g. "bar")
 */
class BlogArticle extends MultipleElement implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.emotion-element > div.blog-element'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'article' => 'div.blog-entry',
            'articleTitle' => 'h2 > a',
            'articleLink' => 'div.blog_img > a',
            'articleText' => 'p'
        ];
    }

    /**
     * Returns all blog articles of the element
     * @param string[] $properties
     * @return array[]
     */
    public function getArticles(array $properties)
    {
        $elements = Helper::findAllOfElements($this, ['article']);

        $articles = [];

        /** @var NodeElement $article */
        foreach ($elements['article'] as $article) {
            $articleProperties = [];

            foreach ($properties as $property) {
                $method = 'get' . ucfirst($property) . 'Property';
                $articleProperties[$property] = $this->$method($article);
            }

            $articles[] = $articleProperties;
        }

        return $articles;
    }

    /**
     * Returns the title of the blog article
     * @param NodeElement $article
     * @return string
     */
    public function getTitleProperty(NodeElement $article)
    {
        $selectors = Helper::getRequiredSelectors($this, ['articleTitle', 'articleLink']);

        $title = $article->find('css', $selectors['articleTitle']);

        $titles = [
            'titleTitle' => $title->getAttribute('title'),
            'linkTitle' => $article->find('css', $selectors['articleLink'])->getAttribute('title'),
            'title' => rtrim($title->getText(), '.')
        ];

        return $this->getUniqueTitle($titles);
    }

    /**
     * Returns the image of the blog article
     * @param NodeElement $article
     * @return string|null
     */
    public function getImageProperty(NodeElement $article)
    {
        $selector = Helper::getRequiredSelector($this, 'articleLink');
        return $article->find('css', $selector)->getAttribute('style');
    }

    /**
     * Returns the link to the blog article
     * @param NodeElement $article
     * @return string
     */
    public function getLinkProperty(NodeElement $article)
    {
        $selectors = Helper::getRequiredSelectors($this, ['articleTitle', 'articleLink']);

        $links = [
            'titleLink' => $article->find('css', $selectors['articleTitle'])->getAttribute('href'),
            'link' => $article->find('css', $selectors['articleLink'])->getAttribute('href')
        ];

        return Helper::getUnique($links);
    }

    /**
     * Returns the text preview of the blog article
     * @param NodeElement $article
     * @return null|string
     */
    public function getTextProperty(NodeElement $article)
    {
        $selector = Helper::getRequiredSelector($this, 'articleText');
        return $article->find('css', $selector)->getText();
    }

    /**
     * Helper method to get the unique title
     * @param string[] $titles
     * @return string
     * @throws \Exception
     */
    protected function getUniqueTitle(array $titles)
    {
        $title = array_unique($titles);

        switch (count($title)) {
            //normal case
            case 1:
                return current($title);

            //if blog article name is too long, it will be cut. So it's different from the other and has to be checked separately
            case 2:
                $check = [$title];
                $result = Helper::checkArray($check);
                break;

            default:
                $result = false;
                break;
        }

        if ($result !== true) {
            $messages = ['The blog article has different titles!'];
            foreach ($title as $key => $value) {
                $messages[] = sprintf('"%s" (Key: "%s")', $value, $key);
            }

            Helper::throwException($messages);
        }

        return $title['titleTitle'];
    }
}
