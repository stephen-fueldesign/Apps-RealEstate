/**
 * Delete a real estate listing
 * 
 * @param {number} iListingId - The ID of the listing to delete
 * @returns {boolean} - Whether the deletion was successful
 */
function deleteListing(iListingId)
{
	try
	{
		// Delete the listing
		Components.RealEstate.Listings.delete(iListingId);
		
		// Close the dialog and refresh the parent page
		window.parent.closeDialog();
		window.parent.location.reload();
		
		return true;
	}
	catch (oError)
	{
		alert('Error deleting listing: ' + oError.message);
		return false;
	}
}

/**
 * Delete a real estate listing and optionally its associated page
 * 
 * @param {number} iListingId - The ID of the listing to delete
 * @param {boolean} bDeletePage - Whether to delete the associated page
 * @returns {boolean} - Whether the deletion was successful
 */
function deleteListingAndPage(iListingId, bDeletePage)
{
	try
	{
		// Get the listing to find the page ID
		let arrListing = Components.RealEstate.Listings.get(iListingId);
		let sPageId = null;
		
		if (bDeletePage && arrListing.listing_content_type === 'page')
		{
			sPageId = arrListing.listing_content_url;
		}
		
		// Delete the listing
		Components.RealEstate.Listings.delete(iListingId);
		
		// Delete the page if requested
		if (sPageId)
		{
			try
			{
				Components.Website.Pages.delete(sPageId);
			}
			catch (oPageError)
			{
				console.error('Error deleting page: ' + oPageError.message);
				// Continue even if page deletion fails
			}
		}
		
		// Close the dialog and refresh the parent page
		window.parent.closeDialog();
		window.parent.location.reload();
		
		return true;
	}
	catch (oError)
	{
		alert('Error deleting listing: ' + oError.message);
		return false;
	}
}
