<?php

/**
 * This file is part of the PrestaSitemapBundle
 *
 * (c) PrestaConcept <www.prestaconcept.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Presta\SitemapBundle\Service;

use Presta\SitemapBundle\Event\SitemapPopulateEvent;
use Presta\SitemapBundle\Sitemap;
use Presta\SitemapBundle\Sitemap\Url\Url;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Abstract sitemap generator class
 *
 * @author Konstantin Myakshin <koc-dp@yandex.ru>
 */
abstract class AbstractGenerator
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var Sitemap\Sitemapindex
     */
    protected $root;

    /**
     * @var Sitemap\Urlset[]|Sitemap\DumpingUrlset[]
     */
    protected $urlsets = array();

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * add an Url to an Urlset
     *
     * section is helpfull for partial cache invalidation
     *
     * @param Url    $url
     * @param string $section
     *
     * @throws \RuntimeException
     */
    public function addUrl(Url $url, $section)
    {
        $urlset = $this->getUrlset($section);

        //maximum 50k sitemap in sitemapindex
        $i = 0;
        while ($urlset->isFull() && $i <= Sitemap\Sitemapindex::LIMIT_ITEMS) {
            $urlset = $this->getUrlset($section . '_' . $i);
            $i++;
        }

        if ($urlset->isFull()) {
            throw new \RuntimeException('The limit of sitemapindex has been exceeded');
        }

        $urlset->addUrl($url);
    }

    /**
     * get or create urlset
     *
     * @param string $name
     *
     * @return Sitemap\Urlset
     */
    public function getUrlset($name)
    {
        if (!isset($this->urlsets[$name])) {
            $this->urlsets[$name] = $this->newUrlset($name);
        }

        return $this->urlsets[$name];
    }

    /**
     * Factory method for create Urlsets
     *
     * @param string $name
     * @param \DateTime $lastmod
     *
     * @return Sitemap\Urlset
     */
    abstract protected function newUrlset($name, \DateTime $lastmod = null);

    /**
     * Dispatches SitemapPopulate Event - the listeners should use it to add their URLs to the sitemap
     *
     * @param string|null $section
     * @param string $locale Locale for which URLs should be generated
     */
    protected function populate($section = null, $locale = null)
    {
        $event = new SitemapPopulateEvent($this, $section, $locale);
        $this->dispatcher->dispatch(SitemapPopulateEvent::ON_SITEMAP_POPULATE, $event);
    }

    /**
     * @return Sitemap\Sitemapindex
     */
    protected function getRoot()
    {
        if (null === $this->root) {
            $this->root = new Sitemap\Sitemapindex();

            foreach ($this->urlsets as $urlset) {
                $this->root->addSitemap($urlset);
            }
        }

        return $this->root;
    }
}
