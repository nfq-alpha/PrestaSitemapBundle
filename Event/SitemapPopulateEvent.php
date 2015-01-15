<?php

/*
 * This file is part of the prestaSitemapPlugin package.
 * (c) David Epely <depely@prestaconcept.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Presta\SitemapBundle\Event;

use Presta\SitemapBundle\Service\AbstractGenerator;
use Symfony\Component\EventDispatcher\Event;

/**
 * Manage populate event
 *
 * @author depely
 */
class SitemapPopulateEvent extends Event
{
    const ON_SITEMAP_POPULATE = 'presta_sitemap.populate';

    protected $generator;

    /**
     * Allows creating EventListeners for particular sitemap sections, used when dumping
     *
     * @var string
     */
    protected $section;

    /**
     * @var string Locale for which URLs should be generated
     */
    protected $locale = null;

    /**
     * @param AbstractGenerator $generator
     * @param string $locale Locale for which URLs should be generated
     * @param null $section
     */
    public function __construct(AbstractGenerator $generator, $section = null, $locale = null)
    {
        $this->generator = $generator;
        $this->section = $section;
        $this->locale = $locale;
    }

    /**
     * @return AbstractGenerator
     */
    public function getGenerator()
    {
        return $this->generator;
    }

    /**
     * Section to be processed, null means any
     *
     * @return null|string
     */
    public function getSection()
    {
        return $this->section;
    }

    public function getLocale()
    {
        return $this->locale;
    }
}
