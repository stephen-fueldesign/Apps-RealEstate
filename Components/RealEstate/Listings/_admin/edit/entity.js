/**
 * Handle the form submission for real estate listings
 * 
 * @param {Object} arrFormValues - The form values
 * @param {string} sAction - The action to take after saving (close or edit)
 * @returns {boolean} - Whether the save was successful
 */
function listingsFormOnSave(arrFormValues, sAction)
{
	// Validate the form
	if (!arrFormValues.listing_title)
	{
		alert('Please enter a title for the listing.');
		return false;
	}
	
	// Set the content URL based on the content type
	if (arrFormValues.listing_content_type === 'new' && arrFormValues.listing_content_url_new)
	{
		arrFormValues.listing_content_url = arrFormValues.listing_content_url_new;
	}
	else if (arrFormValues.listing_content_type === 'page' && arrFormValues.listing_content_url_page)
	{
		arrFormValues.listing_content_url = arrFormValues.listing_content_url_page;
	}
	else if (arrFormValues.listing_content_type === 'url' && arrFormValues.listing_content_url_external)
	{
		arrFormValues.listing_content_url = arrFormValues.listing_content_url_external;
	}
	else if (arrFormValues.listing_content_type === 'file' && arrFormValues.listing_content_url_file)
	{
		arrFormValues.listing_content_url = arrFormValues.listing_content_url_file;
	}
	
	// Save the listing
	let bSaved = false;
	let iListingId = arrFormValues.listing_id;
	
	try
	{
		iListingId = Components.RealEstate.Listings.save(arrFormValues);
		bSaved = true;
	}
	catch (oError)
	{
		alert('Error saving listing: ' + oError.message);
		return false;
	}
	
	// Determine what to do next based on the action
	if (bSaved)
	{
		if (sAction === 'edit' && arrFormValues.listing_content_url)
		{
			// Redirect to edit the linked page
			let sUrl = '/admin/website/pages/edit/?page_id=' + arrFormValues.listing_content_url.replace(/^(http)?s?:\/\/?(www\.)?/, '/');
			window.location.href = sUrl;
		}
		else
		{
			// Redirect back to the listings page
			window.location.href = '/admin/realestate/listings/';
		}
	}
	
	return bSaved;
}

/**
 * Generate a page ID for a new page based on the listing title
 * 
 * @param {Object} arrFormValues - The form values
 * @returns {string} - The generated page ID
 */
function generatePageId(arrFormValues)
{
	let sTitle = arrFormValues.listing_title || '';
	
	if (!sTitle)
	{
		return '';
	}
	
	// Convert to lowercase and replace spaces with dashes
	let sPageId = sTitle.toLowerCase().replace(/\s+/g, '-');
	
	// Remove any special characters
	sPageId = sPageId.replace(/[^a-z0-9-]/g, '');
	
	// Ensure it starts with a slash
	if (sPageId.charAt(0) !== '/')
	{
		sPageId = '/' + sPageId;
	}
	
	return sPageId;
}

/**
 * Generate text to display for the page ID
 * 
 * @param {Object} arrFormValues - The form values
 * @returns {string} - The text to display
 */
function generatePageIdText(arrFormValues)
{
	let sPageId = generatePageId(arrFormValues);
	
	if (!sPageId)
	{
		return 'Please enter a title to generate a page ID';
	}
	
	return 'This listing will create a new page at: <strong>' + sPageId + '</strong>';
}
