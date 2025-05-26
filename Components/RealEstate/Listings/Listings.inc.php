<?php

/**
 * Apache License
 *
 * Version 2.0, January 2004
 * http://www.apache.org/licenses/
 */
namespace Components\RealEstate;

/**
 * Listings
 *
 * Use this API to create, update and delete real estate listings.
 *
 * @public_api
 * @component_entity_type Listing
 * @component_feature RealEstate
 */
class Listings extends \Framework\Components\DataABC
{
	/**
	 * @inheritdoc
	 */
	public static function fieldsGetPrimaryKey()
	{
		return 'listing_id';
	}

	/**
	 * @inheritdoc
	 */
	public static function getOrder()
	{
		return [['listing_date', 'desc'], ['listing_id', 'desc']];
	}

	/**
	 * @inheritdoc
	 */
	public static function dbGetTableName()
	{
		return 'realestate_listings';
	}

	/**
	 * @inheritdoc
	 */
	public static function fieldsGetPrefix()
	{
		return 'listing_';
	}

	/**
	 * @inheritdoc
	 */
	public static function fieldsGetAllImages()
	{
		return ['listing_featured_image'];
	}

	/**
	 * @inheritdoc
	 */
	public static function fieldsGetAllHTML()
	{
		return ['listing_description_short', 'listing_description'];
	}

	/**
	 * @inheritdoc
	 */
	public static function fieldsGetOffline()
	{
		return 'listing_is_offline';
	}

	/**
	 * @inheritdoc
	 */
	public static function optionShouldRecordChanges()
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public static function eventGetHandlers()
	{
		$arrHandlers = [];

		// On page move
		$arrHandlers['\Components\Website\Pages']['postMove'] = function ($strPageIdFrom, $strPageIdTo)
		{
			$strNewURL = \Components\Website\Pages::getColumn($strPageIdTo, 'page_url');

			if (!$strNewURL)
				$strNewURL = $strPageIdTo;

			\Components\RealEstate\Listings::moveFullVersionPageIds($strPageIdFrom, $strNewURL);
		};

		// On page delete
		$arrHandlers['\Components\Website\Pages']['postDelete'] = function ($strPageId)
		{
			// Unlink the listings
			$arrFilter = [['listing_content_url', '=', $strPageId]];

			foreach (\Components\RealEstate\Listings::getAll(['filter' => $arrFilter]) as $arrListing)
			{
				$arrListing['listing_content_type'] = 'none';
				$arrListing['listing_content_url'] = null;
				\Components\RealEstate\Listings::save($arrListing, false);
			}
		};

		// Contact Merge handler
		$arrHandlers['\Components\Customers\Contacts']['postMerge'] = function ($arrOldContactIds, $iNewContactId)
		{
			$arrFilter = ['contact_id', 'IN', $arrOldContactIds];

			foreach (\Components\RealEstate\Listings::getAll(['filter' => $arrFilter]) as $arrEntity)
			{
				\Components\RealEstate\Listings::saveColumn($arrEntity['listing_id'], 'contact_id', $iNewContactId);
			}
		};

		return $arrHandlers;
	}

	/**
	 * @inheritdoc
	 */
	protected static function _save(& $arrEntity, $strReason = null)
	{
		$arrEntityOrig = $arrEntity;
		$arrExisting = null;

		// Load the existing listing
		if (isset($arrEntity['listing_id']))
			$arrExisting = static::get($arrEntity['listing_id']);

		// Set the date
		if (!isset($arrEntity['listing_id']) && !isset($arrEntity['listing_date']))
			$arrEntity['listing_date'] = dbDateTime(time());

		// Author
		if (!$arrExisting && !array_key_exists('contact_id', $arrEntity))
			$arrEntity['contact_id'] = \Components\Customers\Contacts::currentContactId();

		// Listings content type
		if (isset($arrEntity['listing_content_type']))
		{
			// If none, then nullify the url
			if ($arrEntity['listing_content_type'] == 'none')
				$arrEntity['listing_content_url'] = null;
		}

		// Save the listing
		$bResult = parent::_save($arrEntity, $strReason);

		// Handle content URL
		if ($bResult && isset($arrEntity['listing_content_type']))
		{
			$sContentType = $arrEntity['listing_content_type'];
			$sContentURL = null;

			// Handle page creation
			if ($sContentType == 'new' && isset($arrEntity['listing_content_url_new']))
			{
				$sPageId = $arrEntity['listing_content_url_new'];

				// Create a page
				$arrPage = [
					'page_id' => $sPageId,
					'page_title' => $arrEntity['listing_title'],
					'page_content' => isset($arrEntity['listing_description']) ? $arrEntity['listing_description'] : '',
					'page_search_description' => isset($arrEntity['listing_description_short']) ? strip_tags($arrEntity['listing_description_short']) : '',
				];

				// Save the page
				\Components\Website\Pages::save($arrPage);

				// Update the content URL
				$sContentURL = $sPageId;
				\Components\RealEstate\Listings::saveColumn($arrEntity['listing_id'], 'listing_content_url', $sContentURL);
				\Components\RealEstate\Listings::saveColumn($arrEntity['listing_id'], 'listing_content_type', 'page');
				\Components\RealEstate\Listings::saveColumn($arrEntity['listing_id'], 'listing_created_page', 1);
			}
			// Handle page selection
			else if ($sContentType == 'page' && isset($arrEntity['listing_content_url_page']))
			{
				$sContentURL = $arrEntity['listing_content_url_page'];
				\Components\RealEstate\Listings::saveColumn($arrEntity['listing_id'], 'listing_content_url', $sContentURL);
			}
			// Handle external URL
			else if ($sContentType == 'url' && isset($arrEntity['listing_content_url_external']))
			{
				$sContentURL = $arrEntity['listing_content_url_external'];
				\Components\RealEstate\Listings::saveColumn($arrEntity['listing_id'], 'listing_content_url', $sContentURL);
			}
			// Handle file upload
			else if ($sContentType == 'file' && isset($arrEntity['listing_content_url_file']))
			{
				$sContentURL = $arrEntity['listing_content_url_file'];
				\Components\RealEstate\Listings::saveColumn($arrEntity['listing_id'], 'listing_content_url', $sContentURL);
			}
		}

		return $bResult;
	}

	/**
	 * Move page IDs for full version listings
	 *
	 * @param string $sOldPageId
	 * @param string $sNewPageId
	 */
	public static function moveFullVersionPageIds($sOldPageId, $sNewPageId)
	{
		$arrFilter = [['listing_content_url', '=', $sOldPageId]];

		foreach (static::getAll(['filter' => $arrFilter]) as $arrListing)
		{
			static::saveColumn($arrListing['listing_id'], 'listing_content_url', $sNewPageId);
		}
	}

	/**
	 * Render a listing in the admin interface
	 *
	 * @param array $arrListing
	 * @return string
	 */
	public static function renderAdmin($arrListing)
	{
		return '<a href="/admin/realestate/listings/edit/?listing_id=' . $arrListing['listing_id'] . '">' . $arrListing['listing_title'] . '</a>';
	}

	public function install()
	{
		parent::install();
	}
}
