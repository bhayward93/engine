<?php
namespace Minds\Entities;

use Minds\Core;
use Minds\Core\Events\Dispatcher;
use Minds\Helpers;

class Blog extends \ElggObject
{
    protected $dirtyIndexes = false;

    /**
     * Set subtype to blog.
     */
    protected function initializeAttributes()
    {
        parent::initializeAttributes();

        $this->attributes['subtype'] = "blog";
        $this->attributes['mature'] = false;
        $this->attributes['boost_rejection_reason'] = -1;
        $this->attributes['spam'] = false;
        $this->attributes['deleted'] = false;
        $this->attributes['wire_threshold'] = null;
        $this->attributes['paywall'] = null;
        $this->attributes['categories'] = [];
        $this->attributes['published'] = false;
        $this->attributes['last_save'] = null;
        $this->attributes['draft_access_id'] = 0;
    }

    /**
     * Return an array of fields which can be exported.
     *
     * @return array
     */
    public function getExportableValues()
    {
        return array_merge(parent::getExportableValues(), array(
            'last_updated',
            'excerpt',
            'license',
            'ownerObj',
            'header_bg',
            'header_top',
            'monetized',
            'mature',
            'boost_rejection_reason',
            'wire_threshold',
            'categories',
            'published',
            'last_save',
            'draft_access_id',
        ));
    }

    /**
     * Icon URL
     */
    public function getIconURL($size = '')
    {
        if ($this->header_bg) {
            global $CONFIG;
            $base_url = Core\Config::build()->cdn_url ? Core\Config::build()->cdn_url : elgg_get_site_url();
            $image = elgg_get_site_url() . 'fs/v1/banners/' .  $this->guid . '/'.$this->last_updated;

            return $image;
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->strictErrorChecking = false;
        $dom->loadHTML($this->description);
        $nodes = $dom->getElementsByTagName('img');
        foreach ($nodes as $img) {
            $image = $img->getAttribute('src');
        }
        $base_url = Core\Config::build()->cdn_url ? Core\Config::build()->cdn_url: elgg_get_site_url();
        $image = $base_url . 'thumbProxy?src='. urlencode($image) . '&c=2708';
        if ($width) {
            $image .= '&width=' . $width;
        }
        return $image;
    }

    /**
     * Sets the maturity flag for this activity
     * @param mixed $value
     */
    public function setMature($value)
    {
        $this->mature = (bool) $value;
        return $this;
    }

    /**
     * Gets the maturity flag
     * @return boolean
     */
    public function getMature()
    {
        return (bool) $this->mature;
    }

    public function setBoostRejectionReason($reason)
    {
        $this->boost_rejection_reason = (int) $reason;
        return $this;
    }

    public function getBoostRejectionReason()
    {
        return (int) $this->boost_rejection_reason;
    }

    /**
     * Sets the spam flag for this blog
     * @param mixed $value
     */
    public function setSpam($value)
    {
        $this->spam = (bool) $value;
        return $this;
    }

    /**
     * Gets the spam flag
     * @return boolean
     */
    public function getSpam()
    {
        return (bool) $this->spam;
    }

    /**
     * Sets the deleted flag for this blog
     * @param mixed $value
     */
    public function setDeleted($value)
    {
        $this->deleted = (bool) $value;
        $this->dirtyIndexes = true;
        return $this;
    }

    /**
     * Gets the deleted flag
     * @return boolean
     */
    public function getDeleted()
    {
        return (bool) $this->deleted;
    }

    /**
     * Gets wire threshold
     * @return mixed
     */
    public function getWireThreshold()
    {
        return $this->wire_threshold;
    }

    /**
     * Sets wire threshold
     * @param $wire_threshold
     * @return $this
     */
    public function setWireThreshold($wire_threshold)
    {
        $this->wire_threshold = $wire_threshold;
        return $this;
    }

    /**
     * Sets if there is a paywall or not
     * @param mixed $value
     */
    public function setPayWall($value)
    {
        $this->paywall = (bool) $value;
        return $this;
    }

    /**
     * @return array categories
     */
    public function getCategories() {
        return $this->categories ? $this->categories : [];
    }

    /**
     * @param array $categories
     * @return $this
     */
    public function setCategories($categories) {
        $this->categories = $categories;
        return $this;
    }

    /**
     * Checks if there is a paywall for this post
     * @return boolean
     */
    public function isPayWall()
    {
        return (bool) $this->paywall;
    }

    /**
     * Return the url for this entity
     */
    public function getUrl()
    {
        return elgg_get_site_url() . 'blog/view/' . $this->guid;
    }

    public function save($index = true)
    {
        if ($this->getDeleted()) {
            $index = false;

            if ($this->dirtyIndexes) {
                $indexes = $this->getIndexKeys(true);

                $db = new Core\Data\Call('entities_by_time');
                foreach ($indexes as $idx) {
                    $db->removeAttributes($idx, [$this->guid]);
                }
            }
        } else {
            if ($this->dirtyIndexes) {
                // Re-add to indexes, force as true
                $index = true;
            }
        }

        return parent::save($index);
    }

    public function export()
    {
        $export = parent::export();
        if (!$this->title) {
            $export['title'] = "";
        }
        $export['thumbnail_src'] = $this->getIconUrl();
        $export['description'] = $this->description; //blogs need to be able to export html
        $export['thumbs:up:user_guids'] = (array) array_values($export['thumbs:up:user_guids'] ?: []);
        $export['thumbs:up:count'] = Helpers\Counters::get($this->guid, 'thumbs:up');
        $export['thumbs:down:count'] = Helpers\Counters::get($this->guid, 'thumbs:down');
        $export['mature'] = (bool) $export['mature'];
        $export['boost_rejection_reason'] = $this->getBoostRejectionReason();
        $export['monetized'] = (bool) $export['monetized'];
        $export['categories'] = $this->getCategories();
        $export['wire_threshold'] = $this->getWireThreshold();
        $export['paywall'] = $this->isPaywall();

        if ($export['published'] != "") {
            $export['published'] = (bool) $export['published'];
        } else {
            $export['published'] = true;
        }

        if (Helpers\Flags::shouldDiscloseStatus($this)) {
            $export['spam'] = $this->getSpam();
            $export['deleted'] = $this->getDeleted();
        }

        $export = array_merge($export, Dispatcher::trigger('export:extender', 'blog', ['entity'=>$this], []));

        return $export;
    }
}
